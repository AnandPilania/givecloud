<?php

namespace Ds\Common;

use Creativeorange\Gravatar\Exceptions\InvalidEmailException;
use Creativeorange\Gravatar\Facades\Gravatar;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\EmailParser;
use Egulias\EmailValidator\EmailValidator as EguliasValidator;
use Egulias\EmailValidator\Exception\InvalidEmail;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;
use EmailValidation\EmailAddress;
use EmailValidation\EmailDataProvider;
use EmailValidation\EmailValidator as DaveearleyValidator;
use EmailValidation\ValidationResults;
use EmailValidation\Validations\EmailHostValidator;
use EmailValidation\Validations\FreeEmailServiceValidator;
use EmailValidation\Validations\MisspelledEmailValidator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Iodev\Whois\Whois;
use Throwable;

class EmailValidator
{
    /**
     * Check various aspects to an emails validity.
     *
     * @param string $email
     * @return array
     */
    public function checkEmail(string $email): array
    {
        $parts = $this->parts($email);

        $data = [
            'status' => 'valid',
            'address' => $email,
            'username' => Arr::get($parts, 'local'),
            'domain' => Arr::get($parts, 'domain'),
            'md5_hash' => md5($email),
            'suggestion' => $this->suggestions($email),
            'valid_format' => $this->isValid($email),
            'suspicious' => $this->isSuspicious($email),
            'host_exists' => $this->isValidHost($email),
            'mx_found' => $this->hasMxRecords($email),
            'mx_record' => $this->preferredMxRecord($email),
            'dmarc_reject' => $this->hasDmarcRejectPolicy($email),
            'spf_restricted' => $this->hasSpfRestrictions($email),
            'domain_age' => $this->domainAge($email),
            // 'smtp_provider'=> null,
            // 'deliverable'  => null,
            // 'full_inbox'   => null,
            // 'catch_all'    => null,
            'gravatar' => $this->hasGravatar($email),
            'role' => $this->isRole($email),
            'disposable' => $this->isDisposable($email),
            'free' => $this->isFree($email),
        ];

        switch (true) {
            case (bool) ($data['mx_found']) !== true: $data['status'] = 'no_dns_entries'; break;
            case (bool) ($data['role']) === true: $data['status'] = 'role_based'; break;
            case (bool) ($data['disposable']) === true: $data['status'] = 'disposable'; break;
            case (bool) ($data['suggestion']) === true: $data['status'] = 'possible_typo'; break;
            case (bool) ($data['suspicious']) === true: $data['status'] = 'suspicious'; break;
            case (bool) ($data['valid_format']) !== true: $data['status'] = 'invalid'; break;
        }

        return $data;
    }

    /**
     * Get the age of the domain.
     *
     * @param string $email
     * @return int
     */
    public function domainAge(string $email): int
    {
        $domain = $this->parts($email, 'domain');

        return Cache::tags('email')->rememberForever(
            'domain_age:' . md5($domain),
            function () use ($domain) {
                try {
                    $created = Whois::create()->loadDomainInfo($domain)->creationDate;

                    return $created ? \Carbon\Carbon::createFromTimestampUTC($created)->diffInDays('now') : 0;
                } catch (Throwable $exception) {
                    return 0;
                }
            }
        );
    }

    /**
     * Checks DNS records to see if the domain is has a DMARC record
     * and is using the reject policy.
     *
     * @param string $email
     * @return bool
     */
    public function hasDmarcRejectPolicy(string $email): bool
    {
        $domain = $this->parts($email, 'domain');

        return Cache::tags('email')->remember(
            'dmarc_reject:' . md5($domain),
            now()->addDay(),
            function () use ($domain, $email) {
                if ($this->hasMxRecords($email)) {
                    foreach (dns_get_record("_dmarc.$domain", DNS_TXT) as $record) {
                        if (Str::contains($record['txt'] ?? '', 'p=reject')) {
                            return true;
                        }
                    }
                }

                return false;
            }
        );
    }

    /**
     * Check if email has a Gravatar.
     *
     * @param string $email
     * @return bool
     */
    public function hasGravatar(string $email): bool
    {
        try {
            return (bool) Gravatar::exists($email);
        } catch (InvalidEmailException $e) {
            return false;
        }
    }

    /**
     * Check if email has MX records.
     *
     * @param string $email
     * @return bool
     */
    public function hasMxRecords(string $email): bool
    {
        return (bool) $this->preferredMxRecord($email);
    }

    /**
     * Checks DNS records to see if the domain is has any SPF record
     * restrictions that would block SendGrid emails.
     *
     * @param string $email
     * @return bool
     */
    public function hasSpfRestrictions(string $email): bool
    {
        $domain = $this->parts($email, 'domain');

        return Cache::tags('email')->remember(
            'spf_resolver:' . md5($domain),
            now()->addDay(),
            function () use ($domain, $email) {
                if ($this->hasMxRecords($email)) {
                    return (new SpfResolver($domain))->hasFailurePolicy();
                }

                return false;
            }
        );
    }

    /**
     * Check the deliverability of an email address.
     *
     * This is complicated by the fact that GCP blocks all outgoing
     * traffic on port 25 so it's impossible to actually do the checks
     *
     * @see https://github.com/zytzagoo/smtp-validate-email
     * @see https://github.com/ddtraceweb/smtp-validator-email
     *
     * @param string $email
     * @return bool
     */
    public function isDeliverable(string $email): bool
    {
        // $validator = new \SMTPValidateEmail\Validator;
        // $results = $validator3->validate($email, 'admin@example.com');
        // return (bool) Arr::get($results, $email);
        // return (bool) Arr::get($results, "domains.$domain.catchall");

        return $this->hasMxRecords($email);
    }

    /**
     * Check if email is a disposable address.
     *
     * @param string $email
     * @return bool
     */
    public function isDisposable(string $email): bool
    {
        return (bool) false;
    }

    /**
     * Check if email is a free address.
     *
     * @param string $email
     * @return bool
     */
    public function isFree(string $email): bool
    {
        $validator = new DaveearleyValidator(
            new EmailAddress($email),
            new ValidationResults,
            new EmailDataProvider
        );

        $validator->registerValidator(new FreeEmailServiceValidator);
        $daveearley = $validator->getValidationResults()->asArray();

        return (bool) Arr::get($daveearley, 'free_email_provider');
    }

    /**
     * Check if email is a role-based address.
     *
     * @param string $email
     * @return bool
     */
    public function isRole(string $email): bool
    {
        return (bool) false;
    }

    /**
     * Check if email is suspicious.
     *
     * @param string $email
     * @return bool
     */
    public function isSuspicious(string $email): bool
    {
        return ! (new EguliasValidator)->isValid($email, new SpoofCheckValidation);
    }

    /**
     * Check if email is valid.
     *
     * @param string $email
     * @return bool
     */
    public function isValid(string $email): bool
    {
        return (new EguliasValidator)->isValid($email, new RFCValidation);
    }

    /**
     * Check if email has a valid host.
     *
     * @param string $email
     * @return bool
     */
    public function isValidHost(string $email): bool
    {
        $domain = $this->parts($email, 'domain');

        return Cache::tags('email')->remember(
            'valid_host:' . md5($domain),
            now()->addDay(),
            function () use ($email) {
                $validator = new DaveearleyValidator(
                    new EmailAddress($email),
                    new ValidationResults,
                    new EmailDataProvider
                );

                $validator->registerValidator(new EmailHostValidator);
                $daveearley = $validator->getValidationResults()->asArray();

                return (bool) Arr::get($daveearley, 'valid_host');
            }
        );
    }

    /**
     * Get the parts of the email.
     *
     * @param string $email
     * @param string $part
     * @return array|string
     */
    public function parts(string $email, string $part = null)
    {
        try {
            $parts = (new EmailParser(new EmailLexer))->parse($email);
        } catch (InvalidEmail $invalid) {
            $parts = ['local' => null, 'domain' => null];
        }

        return $part ? Arr::get($parts, $part) : $parts;
    }

    /**
     * Get the preferred MX record.
     *
     * @param string $email
     * @return string|null
     */
    public function preferredMxRecord(string $email): ?string
    {
        $domain = $this->parts($email, 'domain');

        return Cache::tags('email')->remember(
            'mx_records:' . md5($domain),
            now()->addDay(),
            function () use ($domain) {
                return collect(dns_get_record($domain, DNS_MX) ?: null)
                    ->sortBy('pri')
                    ->pluck('target')
                    ->first();
            }
        );
    }

    /**
     * Check for suggestions of alternate spellings for an email.
     *
     * @param string $email
     * @return string|null
     */
    public function suggestions(string $email): ?string
    {
        $validator = new DaveearleyValidator(
            new EmailAddress($email),
            new ValidationResults,
            new EmailDataProvider
        );

        $validator->registerValidator(new MisspelledEmailValidator);
        $daveearley = $validator->getValidationResults()->asArray();

        return Arr::get($daveearley, 'possible_email_correction') ?: null;
    }
}

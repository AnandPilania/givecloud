<?php

namespace Ds\Services;

use Ds\Common\SpfResolver;
use Email\Parse;
use Illuminate\Support\Str;
use Swift_Validate;

class EmailService
{
    /** @var \Email\Parse */
    protected $validationInstance = null;

    /**
     * Checks DNS records to see if the domain is has a DMARC record
     * and is using the reject policy.
     *
     * @param string $domain
     * @return bool
     */
    public function hasDmarcRejectPolicy(string $domain): bool
    {
        foreach (dns_get_record("_dmarc.$domain", DNS_TXT) as $record) {
            if (Str::contains($record['txt'] ?? '', 'p=reject')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks DNS records to see if the domain is has any SPF record
     * restrictions that would block SendGrid emails.
     *
     * @param string $domain
     * @return bool
     */
    public function hasSpfRestrictions(string $domain): bool
    {
        $resolver = new SpfResolver($domain);

        if (! $resolver->hasFailurePolicy()) {
            return false;
        }

        $includes = $resolver->getIncludes();

        return ! (
            in_array('_spf.givecloud.co', $includes) ||
            in_array('smtp.givecloud.co', $includes) ||
            in_array('sendgrid.net', $includes)
        );
    }

    /**
     * Checks a string for multiple email addresses and
     * return an array of them.
     *
     * @param string $emailList
     * @return array
     */
    public function parseEmailList(string $emailList)
    {
        if (! $this->validationInstance) {
            $this->validationInstance = Parse::getInstance();
        }

        $emails = $this->validationInstance->parse($emailList);

        $addresses = [];

        foreach ($emails['email_addresses'] as $data) {
            if (! $data['invalid']) {
                $addresses[] = [
                    'name' => $data['name'],
                    'email' => $data['simple_address'],
                ];
            }
        }

        return $addresses;
    }

    /**
     *  Given a string of comma-seperated emails, will return
     *  an array of the valid ones
     */
    public function getValidEmailsFromString(?string $emails = null): array
    {
        return collect(explode(',', (string) $emails))
            ->map(function ($email) {
                return trim($email);
            })->filter(function ($email) {
                return $email && Swift_Validate::email($email);
            })->toArray();
    }
}

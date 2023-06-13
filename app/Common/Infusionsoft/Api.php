<?php

namespace Ds\Common\Infusionsoft;

use Closure;
use DateTimeInterface;
use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infusionsoft\Http\HttpException as InfusionsoftHttpException;
use Infusionsoft\Infusionsoft as InfusionsoftPhpSdk;
use Infusionsoft\TokenExpiredException;

class Api
{
    /** @var \Infusionsoft\Infusionsoft */
    protected $infusionsoft;

    /**
     * Create an instance.
     */
    public function __construct()
    {
        $this->infusionsoft = new InfusionsoftPhpSdk([
            'clientId' => config('services.infusionsoft.client_id'),
            'clientSecret' => config('services.infusionsoft.client_secret'),
            'redirectUri' => config('services.infusionsoft.redirect'),
            'debug' => config('services.infusionsoft.debug'),
        ]);

        $this->useToken();
    }

    /**
     * Retrieve Contact details.
     *
     * @param int $contactId
     * @param array $optionalProperities
     * @return object
     */
    public function getContact($contactId, array $optionalProperities = [])
    {
        return $this->request(function ($client) use ($contactId, $optionalProperities) {
            return $client->contacts()->find($contactId, $optionalProperities);
        });
    }

    /**
     * Retrieve Contact details.
     *
     * @param int $contactId
     * @return object
     */
    public function getContactTags($contactId)
    {
        return $this->request(function ($client) use ($contactId) {
            return $client->contacts()
                ->mock(['id' => $contactId])
                ->tags();
        });
    }

    /**
     * Search for Contacts.
     *
     * @param array $wheres
     * @return array
     */
    public function contactsWhere(array $wheres)
    {
        return $this->request(function ($client) use ($wheres) {
            $contacts = $client->contacts();

            foreach ($wheres as $key => $value) {
                $contacts->where($key, $value);
            }

            return $contacts->get();
        });
    }

    /**
     * Add Contact details.
     *
     * @param array $data
     * @param bool $dupCheck
     * @return object
     */
    public function addContact(array $data, $dupCheck = false)
    {
        if ($dupCheck) {
            $data['duplicate_option'] = 'Email';
        }

        return $this->request(function ($client) use ($data, $dupCheck) {
            return $client->contacts()->create($data, $dupCheck);
        });
    }

    /**
     * Update Contact details.
     *
     * @param int $contactId
     * @param array $data
     * @return mixed
     */
    public function updateContact($contactId, $data)
    {
        $data['id'] = $contactId;

        return $this->request(function ($client) use ($data) {
            return $client->contacts()->create($data, false);
        });
    }

    /**
     * Opt-in an Email Address.
     *
     * @param string $email
     * @param string $optInReason
     * @return mixed
     */
    public function optIn(string $email, string $optInReason)
    {
        return $this->request(function ($client) use ($email, $optInReason) {
            return $client->emails('xml')->optIn($email, $optInReason);
        });
    }

    /**
     * Add Tag to a Contact.
     *
     * @param int $contactId
     * @param int|array $tagIds
     * @return bool
     */
    public function addTags($contactId, $tagIds)
    {
        return $this->request(function ($client) use ($contactId, $tagIds) {
            return $client->contacts()
                ->mock(['id' => $contactId])
                ->addTags($tagIds);
        });
    }

    /**
     * Remove Tag from a Contact.
     *
     * @param int $contactId
     * @param int|int[] $tagIds
     * @return bool
     */
    public function removeTags($contactId, $tagIds)
    {
        return $this->request(function ($client) use ($contactId, $tagIds) {
            return $client->contacts()
                ->mock(['id' => $contactId])
                ->removeTags($tagIds);
        });
    }

    /**
     * Search for Tags.
     *
     * @param array $wheres
     * @return array
     */
    public function tagsWhere(array $wheres)
    {
        return $this->request(function ($client) use ($wheres) {
            $tags = $client->tags();

            foreach ($wheres as $key => $value) {
                $tags->where($key, $value);
            }

            return $tags->get();
        });
    }

    /**
     * Perform data add.
     *
     * @param string $table
     * @param array $values
     * @return array
     */
    public function add($table, array $values)
    {
        return $this->request(function ($client) use ($table, $values) {
            return $client->data()->add($table, $values);
        });
    }

    /**
     * Perform data load.
     *
     * @param string $table
     * @param int $recordId
     * @param array $wantedFields
     * @return array
     */
    public function load($table, $recordId, array $wantedFields)
    {
        return $this->request(function ($client) use ($table, $recordId, $wantedFields) {
            return $client->data()->load($table, $recordId, $wantedFields);
        });
    }

    /**
     * Perform data update.
     *
     * @param string $table
     * @param int $recordId
     * @param array $values
     * @return array
     */
    public function update($table, $recordId, array $values)
    {
        return $this->request(function ($client) use ($table, $recordId, $values) {
            return $client->data()->update($table, $recordId, $values);
        });
    }

    /**
     * Perform data delete.
     *
     * @param string $table
     * @param int $recordId
     * @return bool
     */
    public function delete($table, $recordId)
    {
        return $this->request(function ($client) use ($table, $recordId) {
            return $client->data()->delete($table, $recordId);
        });
    }

    /**
     * Perform data query.
     *
     * @param string $table
     * @param array $queryData
     * @param array $selectedFields
     * @param string $orderBy
     * @param bool $ascending
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function query($table, array $queryData, array $selectedFields, $orderBy = 'Id', $ascending = false, $limit = 100, $page = 0)
    {
        return $this->request(function ($client) use ($table, $limit, $page, $queryData, $selectedFields, $orderBy, $ascending) {
            return $client->data()->query($table, $limit, $page, $queryData, $selectedFields, $orderBy, $ascending);
        });
    }

    /**
     * Get the authorization URL.
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        return $this->infusionsoft->getAuthorizationUrl(sys_get('ds_account_name'));
    }

    /**
     * Attempts to use a token that was previously cached.
     *
     * @return void
     */
    protected function useToken()
    {
        $token = sys_get('infusionsoft_token');

        if ($token) {
            $this->infusionsoft->setToken($token);
        }
    }

    /**
     * Save the token for later use.
     *
     * @return void
     */
    protected function saveToken()
    {
        $token = $this->infusionsoft->getToken();

        if ($token) {
            sys_set(['infusionsoft_token' => $token]);
        }
    }

    /**
     * Request a token for authentication code.
     *
     * @param string $code
     * @return void
     */
    public function requestToken(string $code)
    {
        $this->infusionsoft->requestAccessToken($code);
        $this->saveToken();
    }

    /**
     * Refresh the token and save for later use.
     *
     * @return void
     */
    public function refreshToken()
    {
        $this->infusionsoft->refreshAccessToken();
        $this->saveToken();
    }

    /**
     * Perform an API request refreshing the token if it has expired.
     *
     * @param \Closure $fn
     * @return mixed
     */
    protected function request(Closure $fn)
    {
        $token = $this->infusionsoft->getToken();

        if ($token) {
            // Before making the request, we can make sure that the token is still
            // valid by doing a check on the end of life.
            if ($token->isExpired()) {
                $this->refreshToken();
            }

            try {
                return $this->castResponse($fn($this->infusionsoft));
            } catch (TokenExpiredException $e) {
                // If the request fails due to an expired access token, we can refresh
                // the token and then do the request again.
                $this->refreshToken();

                return $this->castResponse($fn($this->infusionsoft));
            } catch (InfusionsoftHttpException $e) {
                if ($e->getCode() == 404) {
                    throw new ModelNotFoundException;
                }

                throw (new HttpException($e->getMessage(), $e->getCode()))->setLogs($this->infusionsoft->getLogs());
            }
        }

        throw new MessageException('Infusionsoft request failed. Authorization required.');
    }

    /**
     * Cast associative arrays into objects.
     *
     * @param mixed $response
     * @return mixed
     */
    protected function castResponse($response)
    {
        if (is_array($response)) {
            array_walk_recursive($response, function (&$item) {
                if (is_object($item) && $item instanceof DateTimeInterface) {
                    $item = $item->format('c');
                }
            });
        }

        return json_decode(json_encode($response));
    }
}

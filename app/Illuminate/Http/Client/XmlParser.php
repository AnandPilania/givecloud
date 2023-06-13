<?php

namespace Ds\Illuminate\Http\Client;

use DOMDocument;
use SimpleXMLElement;
use Throwable;

class XmlParser
{
    public function __invoke(string $body, array $config = []): ?SimpleXMLElement
    {
        $useInternalErrors = libxml_use_internal_errors(true);

        // ensure loading of external entities is disable as we are using
        // LIBXML_NONET to enable substituting entities when calling loadXML
        libxml_set_external_entity_loader(function ($public, $system, $context) {
            return null;
        });

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->recover = true;
        $dom->strictErrorChecking = false;
        $dom->validateOnParse = false;

        try {
            $dom->loadXML(
                (string) $body ?: '<root />',
                $config['libxml_options'] ?? LIBXML_NONET
            );

            return simplexml_import_dom($dom);
        } catch (Throwable $e) {
            $message = 'Unable to parse response body into XML: ' . $e->getMessage();

            throw new XmlParseException($message, $e, libxml_get_last_error() ?: null);
        } finally {
            libxml_use_internal_errors($useInternalErrors);
            libxml_set_external_entity_loader(null);
        }
    }
}

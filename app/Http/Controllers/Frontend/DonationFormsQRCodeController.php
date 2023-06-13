<?php

namespace Ds\Http\Controllers\Frontend;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Ds\Models\Product;

class DonationFormsQRCodeController extends Controller
{
    public function __invoke(string $code)
    {
        $data = $this->getQRCode($code, new QROptions([
            'scale' => 25,
            'imageBase64' => false,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        ]));

        return response($data)->withHeaders(['Content-Type' => 'image/png']);
    }

    private function getQRCode(string $code, QROptions $options): string
    {
        $product = Product::query()
            ->donationForms()
            ->hashid($code)
            ->firstOrFail();

        $url = $product->abs_url . '?' . http_build_query(['utm_source' => 'qr']);

        return (new QRCode($options))->render($url);
    }
}

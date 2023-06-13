<?php

namespace Ds\Domain\Theming\Liquid\Filters;

use Ds\Domain\Theming\Liquid\Drops\MediaDrop;
use Ds\Domain\Theming\Liquid\Filters;
use Illuminate\Support\Arr;

class URLFilters extends Filters
{
    /**
     * Returns the URL of a file in the "assets" folder of a theme.
     *
     * @param string $input
     * @return string
     */
    public function asset_url($input)
    {
        $theme = $this->themeService->getEloquentTheme();

        return secure_site_url("/static/{$theme->handle}/assets/$input");
    }

    /**
     * Returns the asset URL of an image in the "assets" folder of a theme.
     *
     * @param string $input
     * @return string
     */
    public function asset_img_url($input)
    {
        return $this->asset_url($input);
    }

    /**
     * Returns the URL of a file in the Files page of the admin.
     *
     * @param string $input
     * @return string
     */
    public static function file_url($input)
    {
        return '';
    }

    /**
     * Returns the asset URL of an image in the Files page of the admin.
     *
     * @param string $input
     * @return string
     */
    public static function file_img_url($input)
    {
        return self::file_url($input);
    }

    /**
     * Generates a link to the account login page.
     *
     * @param string $input
     * @return string
     */
    public static function account_login_link($input)
    {
        return secure_site_url('/account/login');
    }

    /**
     * Generates a link to the account logout page.
     *
     * @param string $input
     * @return string
     */
    public static function account_logout_link($input)
    {
        return secure_site_url('/account/logout');
    }

    /**
     * Generates a link to the account registration page.
     *
     * @param string $input
     * @return string
     */
    public static function account_register_link($input)
    {
        return secure_site_url('/account/register');
    }

    /**
     * Returns the URL of a global asset.
     *
     * @param string $input
     * @return string
     */
    public static function global_asset_url($input)
    {
        return secure_site_url("/jpanel/assets/$input");
    }

    /**
     * Returns the URL of an image.
     *
     * @param mixed $input
     * @return string
     */
    public static function img_url($input, $size = null, $opts = [])
    {
        if ($input instanceof MediaDrop && ($input->is_image || $input->content_type === 'application/pdf')) {
            if (! $size) {
                return $input->thumb;
            }

            return media_thumbnail(
                $input->getSource(),
                array_merge(['size' => $size], Arr::wrap($opts))
            );
        }

        return $input;
    }

    /**
     * Generates an HTML link.
     *
     * @param string $input
     * @param string $link
     * @param string $title
     * @return string
     */
    public static function link_to($input, $link = '#', $title = null)
    {
        $link = ['href="' . e($link) . '"'];

        if ($title) {
            $link[] = 'title="' . e($title) . '"';
        }

        return '<a ' . implode(' ', $link) . '>' . $input . '</a>';
    }

    /**
     * Returns the URL of the payment type's SVG image.
     *
     * @param string $input
     * @return string
     */
    public static function payment_type_img_url($input)
    {
        return '';
    }

    /**
     * Returns the URL of a global assets that are found on Givecloud's servers
     *
     * @param string $input
     * @return string
     */
    public static function givecloud_asset_url($input)
    {
        return self::global_asset_url($input);
    }
}

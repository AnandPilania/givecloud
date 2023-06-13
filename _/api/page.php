<?php

use Illuminate\Support\Str;

function pageSetup($title = '', $layout = '', $id = 0)
{
    \Ds\Domain\Theming\Liquid\Template::share('pageTitle', $title);
    \Illuminate\Support\Facades\View::share('pageTitle', $title);

    // Membership check
    $memberLoginRequired = is_numeric($id) && $id > 0 && ! member_verify_access('node', $id);

    if ($memberLoginRequired) {
        session()->put('url.website_intended', request()->fullUrl());
        throw new \Ds\Domain\Shared\Exceptions\RedirectException('/account/login');
    }
}

function listMenu($o = [])
{
    $s = [
        'levels' => 99,
        'parentid' => 0,
        'siblingid' => 0,
        'showoffline' => 0,
        'showhidden' => 0,
    ];
    foreach ($s as $opt => $value) {
        if (isset($o[$opt]) && trim($o[$opt]) !== '') {
            $s[$opt] = $o[$opt];
        }
    }

    if ($s['siblingid'] > 0) {
        $qT = db_query(sprintf('SELECT parentid FROM node WHERE id = %d', $s['siblingid']));
        $t = db_fetch_assoc($qT);
        $s['parentid'] = $t['parentid'];
    }
    echo listMenu_iter($s['parentid'], 1, $s['levels'], $o);
}

function listMenu_iter($parentid, $level_ix, $level_max, $o)
{
    if ($level_max > 0 && $level_ix > $level_max) {
        return '';
    }

    $o += [
        'showoffline' => '0',
        'showhidden' => '0',
        'siblingid' => '',
    ];

    // $o defaults
    /*$o_defaults = array(
        'showoffline'=>'0',
        'showhidden'=>'0',
        'siblingid'=>''
    );
    foreach ($o_defaults as $opt=>$value) if (!isset($o[$opt])) $o[$opt] = $value;*/

    $returnStr = '';
    $qM = db_query(sprintf(
        "SELECT n.id,
                IFNULL(n.parentid,0) AS parentid,
                n.title,
                n.url AS serverfile,
                n.target
            FROM `node` n
            LEFT JOIN membership_access ax ON ax.parent_type = 'node' AND ax.parent_id = n.id ## membership access restriction
            WHERE n.isactive != %d
                AND n.ishidden = %d
                AND (n.parentid = %d OR (0 = %d AND n.parentid IS NULL))
                AND n.type != 'revision'
                AND (ax.id IS NULL OR (ax.id IS NOT NULL && ax.membership_id in (%s))) ## membership access restriction
            ORDER BY n.sequence",
        db_real_escape_string($o['showoffline']),
        db_real_escape_string($o['showhidden']),
        db_real_escape_string($parentid),
        db_real_escape_string($parentid),
        ((member_is_logged_in() && member()->groups->where('pivot.is_active', true)->count()) ? member()->groups->where('pivot.is_active', true)->pluck('group_id')->implode(',') : 0) // # membership access restriction
    ));

    if ($qM === false) {
        return '';
    }

    while ($a = db_fetch_assoc($qM)) {
        $returnStr .= '<li class="page-link-' . $a['id'] . ' ' . (($o['siblingid'] == $a['id']) ? 'active' : '') . '">';
        if ($a['serverfile'] != '') {
            $returnStr .= sprintf(
                '<a href="%s" %s>%s</a>',
                secure_site_url($a['serverfile']),
                $a['target'] != '' ? 'target="' . $a['target'] . '"' : '',
                $a['title']
            );
        } else {
            $returnStr .= '<a>' . $a['title'] . '</a>';
        }
        $sMenu = listMenu_iter($a['id'], $level_ix + 1, $level_max, $o);
        if ($sMenu != '') {
            $returnStr .= '<ul>' . $sMenu . '</ul>';
        }
        $returnStr .= '</li>';
    }

    return $returnStr;
}

function pageCurs($parentid)
{
    $returnStr = '';
    $qNode = db_query(sprintf("SELECT distinct n.id,
                                    n.parentid,
                                    ifnull(n.title,'[ blank ]') as title,
                                    n.url AS serverfile,
                                    n.isactive,
                                    n.ishidden,
                                    n.url,
                                    n.target,
                                    (CASE WHEN n.type = 'menu' AND (n.parentid = 0 or ifnull(n.url,'') = '') THEN 'list'
                                            WHEN n.type = 'menu' THEN 'link'
                                            ELSE 'file-o' END) AS `icon`,
                                    (CASE WHEN n.requires_login = 1 THEN 1 ELSE 0 END) as is_locked_by_login,
                                    (CASE WHEN n.requires_login = 1 AND n.hide_menu_link_when_logged_out = 1 THEN 1 ELSE 0 END) as is_hidden_when_logged_out,
                                    (CASE WHEN ifnull(_c.lock_count,0) = 0 THEN 0 ELSE 1 END) as is_locked_by_membership
                                FROM `node` n
                                LEFT JOIN (select parent_type, parent_id, COUNT(*) as lock_count from membership_access where parent_type IN ('product_category','node') group by parent_type, parent_id) as _c
                                    ON ( (_c.parent_type = 'node' AND _c.parent_id = n.id) OR (_c.parent_type = 'product_category' AND _c.parent_id = n.category_id) )
                                WHERE IFNULL(n.parentid,0) = %d
                                    AND n.protected = 0
                                    AND n.type != 'revision'
                                ORDER BY n.sequence", $parentid));

    while ($a = db_fetch_assoc($qNode)) {
        $urlA = $a['serverfile'];
        if ($a['serverfile'] != '') {
            $urlA = secure_site_url($a['serverfile']);
            if (Str::endsWith($urlA, '.php')) {
                $urlA = substr($urlA, 0, strlen($urlA) - 4);
            }
        }
        $returnStr .= '<li class="' . (($a['isactive'] == 0 || $a['ishidden'] == 1 || $a['is_hidden_when_logged_out'] == 1) ? '-hidden-offline ' : '') . (($a['ishidden'] == 1 || $a['is_hidden_when_logged_out'] == 1) ? 'text-muted ' : '') . (($a['isactive'] != 1) ? 'text-danger ' : '') . (($a['icon'] == 'list' and ! $a['parentid']) ? 'top-level-menu ' : '') . '">';
        $returnStr .= '<i class="fa fa-' . $a['icon'] . '"></i> ';
        $returnStr .= '<a href="/jpanel/pages/edit?i=' . $a['id'] . '" class="' . (($a['ishidden'] == 1) ? 'text-muted ' : '') . (($a['isactive'] != 1) ? 'text-danger ' : '') . '">' . $a['title'] . '</a>';
        if ($a['is_locked_by_login'] == '1') {
            $returnStr .= '&nbsp;<i class="fa fa-lock" data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<i class=\'fa fa-lock fa-4x fa-fw pull-left\'></i> This page can only be viewed by logged in supporters."></i>';
        }
        if ($a['is_locked_by_membership'] == '1') {
            $returnStr .= '&nbsp;<i class="fa fa-lock" data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<i class=\'fa fa-lock fa-4x fa-fw pull-left\'></i> This category can only be viewed by supporters with membership."></i>';
        }
        if ($a['isactive'] != '1') {
            $returnStr .= '&nbsp;<i class="fa fa-exclamation-circle text-danger" data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<i class=\'fa fa-exclamation-circle fa-fw fa-4x text-danger pull-left\'></i> This page/link is offline. It is hidden and its url will not work."></i>';
        }
        if ($a['ishidden'] == '1') {
            $returnStr .= '&nbsp;<i class="fa fa-eye-slash text-muted" data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<i class=\'fa fa-eye-slash fa-fw fa-4x pull-left text-muted\'></i> This page/link is hidden from your menu. Links to this page still work."></i>';
        }
        if ($a['is_hidden_when_logged_out'] == '1') {
            $returnStr .= '&nbsp;<i class="fa fa-eye-slash text-muted" data-placement="top" data-toggle="popover" data-trigger="hover" data-content="<i class=\'fa fa-eye-slash fa-fw fa-4x pull-left text-muted\'></i> This page/link is hidden from your menu when users are not logged in."></i>';
        }
        if ($urlA != '') {
            $returnStr .= '&nbsp;<span class="linkPreview hidden-xs hidden-sm"><a href="' . $urlA . '" target="_blank">' . $urlA . '</a></span>';
        }
        $returnStr .= '<ul>' . pageCurs($a['id']) . '</ul>';
        $returnStr .= '</li>';
    }

    return $returnStr;
}

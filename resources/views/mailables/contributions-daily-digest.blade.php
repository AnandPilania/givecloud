@php
  if (isset($fromSubscriber) && $fromSubscriber) {
    $appOrgName = sys_get('clientName');
    $appOrgLocation = implode(', ', array_filter([site()->client->city, site()->client->province, site()->client->country]));
    $appLogoUrl = sys_get('default_logo');
    $marginlessAppLogo = false;
    $appFacebookUrl = volt_setting('header_fb_url');
    $appInstagramUrl = volt_setting('header_instagram_url');
    $appLinkedinUrl = volt_setting('header_linkedin_url');
    $appTwitterUrl = volt_setting('header_twitter_url');
  } else {
    $appOrgName = 'Givecloud';
    $appOrgLocation = 'Ottawa, Ontario, Canada';
    $appLogoUrl = 'https://cdn.givecloud.co/static/etc/gc-logo-anim-hd.gif';
    $marginlessAppLogo = true;
    $appFacebookUrl = 'https://www.facebook.com/Givecloud';
    $appInstagramUrl = 'https://www.instagram.com/givecloud/';
    $appLinkedinUrl = 'https://www.linkedin.com/company/givecloud-com';
    $appTwitterUrl = 'https://twitter.com/givecloud';
  }
@endphp
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
  <title>
  </title>
  <!--[if !mso]><!-->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!--<![endif]-->
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
    #outlook a {
      padding: 0;
    }

    body {
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    table,
    td {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }

    img {
      border: 0;
      height: auto;
      line-height: 100%;
      outline: none;
      text-decoration: none;
      -ms-interpolation-mode: bicubic;
    }

    p {
      display: block;
      margin: 13px 0;
    }
  </style>
  <!--[if mso]>
    <noscript>
    <xml>
    <o:OfficeDocumentSettings>
      <o:AllowPNG/>
      <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
    </xml>
    </noscript>
    <![endif]-->
  <!--[if lte mso 11]>
    <style type="text/css">
      .mj-outlook-group-fix { width:100% !important; }
    </style>
    <![endif]-->
  <!--[if !mso]><!-->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;800&display=swap" rel="stylesheet" type="text/css">
  <style type="text/css">
    @import url(https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;800&display=swap);
  </style>
  <!--<![endif]-->
  <style type="text/css">
    @media only screen and (min-width:480px) {
      .mj-column-per-100 {
        width: 100% !important;
        max-width: 100%;
      }

      .mj-column-per-50 {
        width: 50% !important;
        max-width: 50%;
      }

      .mj-column-per-16-666666666666668 {
        width: 16.666666666666668% !important;
        max-width: 16.666666666666668%;
      }
    }
  </style>
  <style media="screen and (min-width:480px)">
    .moz-text-html .mj-column-per-100 {
      width: 100% !important;
      max-width: 100%;
    }

    .moz-text-html .mj-column-per-50 {
      width: 50% !important;
      max-width: 50%;
    }

    .moz-text-html .mj-column-per-16-666666666666668 {
      width: 16.666666666666668% !important;
      max-width: 16.666666666666668%;
    }
  </style>
  <style type="text/css">
    @media only screen and (max-width:480px) {
      table.mj-full-width-mobile {
        width: 100% !important;
      }

      td.mj-full-width-mobile {
        width: auto !important;
      }
    }
  </style>
  <style type="text/css">
    @media (max-width: 479px) {
      .hide-on-mobile {
        display: none;
      }

      .collapse-columns-on-mobile>table>tbody>tr>td {
        display: block !important;
        width: 100% !important;
      }

      .social-icons td {
        padding: 0 !important;
      }
    }

    @media (max-width: 479px) {
      .hide-on-mobile {
        display: none;
      }

      .vertical-align-top-on-mobile {
        vertical-align: top !important;
      }

      .collapse-columns-on-mobile>table>tbody>tr>td {
        display: block !important;
        width: 100% !important;
      }

      .collapse-columns-on-mobile>table>tbody>tr>td>table>tbody>tr>td {
        padding-top: 10px !important;
        text-align: left !important;
      }
    }
  </style>
</head>

<body style="word-spacing:normal;background-color:#F4F4F4;">
  <div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;"> Yesterday's Fundraising: {{ money($new_revenue)->format('$0,0[.]00 $$$') }} {{ $random_emoji() }}
  </div>
  <div style="background-color:#F4F4F4;">
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:40px 10px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><![endif]--> @if ($marginlessAppLogo)
              <!--[if mso | IE]><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div style="margin:0px auto;max-width:580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:0 10px;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="tight-box-outlook" style="vertical-align:top;width:560px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix tight-box" style="background-color: #FFF; border-radius: 10px; font-size: 0px; text-align: left; direction: ltr; display: inline-block; vertical-align: top; width: 100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                              <tr>
                                <td align="center" class="logo" style="margin: auto; max-height: 100%; max-width: 100%; font-size: 0px; padding: 0; word-break: break-word;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                    <tbody>
                                      <tr>
                                        <td style="width:300px;">
                                          <img alt="{{ $appOrgName }}" height="110" src="{{ $appLogoUrl }}" style="border:0;display:block;outline:none;text-decoration:none;height:110px;width:100%;font-size:14px;" width="300">
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr><![endif]--> @else
              <!--[if mso | IE]><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div style="margin:0px auto;max-width:580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:0 10px;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="box-outlook" style="vertical-align:top;width:560px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix box" style="background-color: #FFF; border-radius: 10px; padding: 10px 20px; font-size: 0px; text-align: left; direction: ltr; display: inline-block; vertical-align: top; width: 100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                              <tr>
                                <td align="center" class="logo" style="margin: auto; max-height: 100%; max-width: 100%; font-size: 0px; padding: 10px 25px; word-break: break-word;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                    <tbody>
                                      <tr>
                                        <td style="width:510px;">
                                          <img alt="{{ $appOrgName }}" height="auto" src="{{ $appLogoUrl }}" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:14px;" width="510">
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr><![endif]--> @endif
              <!--[if mso | IE]><tr><td class="spacer-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="spacer-outlook" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div class="spacer" style="padding: 0px; height: 20px; margin: 0px auto; max-width: 580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:0 10px;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div style="margin:0px auto;max-width:580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:580px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                              <tr>
                                <td style="vertical-align:top;padding-right:10px;padding-left:10px;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="left" class="box" style="background-color: #FFF; border-radius: 10px; font-size: 0px; padding: 10px 25px; word-break: break-word;" bgcolor="#FFF">
                                          <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;">
                                            <p>{{ $greeting }}, <strong>{{ $user->firstname }}</strong> {{ $random_emoji() }}</p>
                                            <p>Here's your Givecloud review of yesterday, {{ $date->format('M j, Y') }}.</p>
                                          </div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div style="margin:0px auto;max-width:580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:290px;" ><![endif]-->
                        <div class="mj-column-per-50 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                              <tr>
                                <td style="vertical-align:top;padding-top:20px;padding-right:10px;padding-left:10px;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="left" class="box" style="background-color: #FFF; border-radius: 10px; font-size: 0px; padding: 10px 25px; word-break: break-word;" bgcolor="#FFF">
                                          <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;">
                                            <div style="margin:10px 0 28px">
                                              <strong>{{ $currency_code }} Revenue</strong><br>
                                              <strong style="font-size:24px">{{ money($combine_revenue)->format('$0,0[.]00') }}</strong> @if ($combine_revenue_change !== null) <div style="color:{{ $combine_revenue_change >= 0 ? '#55b494' : '#ed706f' }}">
                                                <img width="10" src="https://cdn.givecloud.co/s/assets/icons/trend-{{ $combine_revenue_change >= 0 ? 'up-green' : 'down-red' }}.png" alt>
                                                {{ numeral(abs($combine_revenue_change))->format('0,0a') }}% Last Month
                                              </div> @else <div style="color:#828282">No Data for Last Month</div> @endif
                                            </div>
                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:8px">
                                              <tbody>
                                                <tr>
                                                  <td align="left" valign="middle">New</td>
                                                  <td align="right" valign="middle">{{ money($new_revenue)->format('$0,0[.]00') }}</td>
                                                </tr>
                                                <tr>
                                                  <td colspan="2" valign="middle">
                                                    <div class="table-row-divider" style="margin: 8px 0; border-bottom: 1px solid #dedede;"></div>
                                                  </td>
                                                </tr>
                                                <tr>
                                                  <td align="left" valign="middle">Recurring</td>
                                                  <td align="right" valign="middle">{{ money($recurring_revenue)->format('$0,0[.]00') }}</td>
                                                </tr>
                                                <tr>
                                                  <td colspan="2" valign="middle">
                                                    <div class="table-row-divider" style="margin: 8px 0; border-bottom: 1px solid #dedede;"></div>
                                                  </td>
                                                </tr>
                                                <tr>
                                                  <td align="left" valign="middle">Repeat</td>
                                                  <td align="right" valign="middle">{{ money($repeat_revenue)->format('$0,0[.]00') }}</td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:290px;" ><![endif]-->
                        <div class="mj-column-per-50 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                              <tr>
                                <td style="vertical-align:top;padding-top:20px;padding-right:10px;padding-left:10px;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="left" class="box" style="background-color: #FFF; border-radius: 10px; font-size: 0px; padding: 10px 25px; word-break: break-word;" bgcolor="#FFF">
                                          <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;">
                                            <div style="margin:10px 0 28px">
                                              <strong>Supporters</strong><br>
                                              <strong style="font-size:24px">{{ numeral($combine_supporters)->format('0,0') }}</strong> @if ($combine_supporters_change !== null) <div style="color:{{ $combine_supporters_change >= 0 ? '#55b494' : '#ed706f' }}">
                                                <img width="10" src="https://cdn.givecloud.co/s/assets/icons/trend-{{ $combine_supporters_change >= 0 ? 'up-green' : 'down-red' }}.png" alt>
                                                {{ numeral(abs($combine_supporters_change))->format('0,0a') }}% Last Month
                                              </div> @else <div style="color:#828282">No Data for Last Month</div> @endif
                                            </div>
                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom:8px">
                                              <tbody>
                                                <tr>
                                                  <td align="left" valign="middle">New</td>
                                                  <td align="right" valign="middle">{{ numeral($new_supporters)->format('0,0') }}</td>
                                                </tr>
                                                <tr>
                                                  <td colspan="2" valign="middle">
                                                    <div class="table-row-divider" style="margin: 8px 0; border-bottom: 1px solid #dedede;"></div>
                                                  </td>
                                                </tr>
                                                <tr>
                                                  <td align="left" valign="middle">Recurring</td>
                                                  <td align="right" valign="middle">{{ numeral($recurring_supporters)->format('0,0') }}</td>
                                                </tr>
                                                <tr>
                                                  <td colspan="2" valign="middle">
                                                    <div class="table-row-divider" style="margin: 8px 0; border-bottom: 1px solid #dedede;"></div>
                                                  </td>
                                                </tr>
                                                <tr>
                                                  <td align="left" valign="middle">Repeat</td>
                                                  <td align="right" valign="middle">{{ numeral($repeat_supporters)->format('0,0') }}</td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div style="margin:0px auto;max-width:580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:290px;" ><![endif]-->
                        <div class="mj-column-per-50 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                              <tr>
                                <td style="vertical-align:top;padding-top:20px;padding-right:10px;padding-left:10px;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="left" class="box" style="background-color: #FFF; border-radius: 10px; font-size: 0px; padding: 10px 25px; word-break: break-word;" bgcolor="#FFF">
                                          <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;">
                                            <div style="margin:10px 0 8px">
                                              <strong>{{ $date->format('F') }}'s {{ $currency_code }} Revenue</strong><br>
                                              <strong style="font-size:24px">{{ money($month_revenue)->format('$0,0[.]00') }}</strong> @if ($month_revenue_change !== null) <div style="color:{{ $month_revenue_change >= 0 ? '#55b494' : '#ed706f' }}">
                                                <img width="10" src="https://cdn.givecloud.co/s/assets/icons/trend-{{ $month_revenue_change >= 0 ? 'up-green' : 'down-red' }}.png" alt>
                                                {{ numeral(abs($month_revenue_change))->format('0,0a') }}% Last Year
                                              </div> @else <div style="color:#828282">No Data for Last Year</div> @endif
                                            </div>
                                          </div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:290px;" ><![endif]-->
                        <div class="mj-column-per-50 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                              <tr>
                                <td style="vertical-align:top;padding-top:20px;padding-right:10px;padding-left:10px;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="left" class="box" style="background-color: #FFF; border-radius: 10px; font-size: 0px; padding: 10px 25px; word-break: break-word;" bgcolor="#FFF">
                                          <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;">
                                            <div style="margin:10px 0 8px">
                                              <strong>{{ $date->format('F') }}'s Supporters</strong><br>
                                              <strong style="font-size:24px">{{ numeral($month_supporters)->format('0,0') }}</strong> @if ($month_supporters_change !== null) <div style="color:{{ $month_supporters_change >= 0 ? '#55b494' : '#ed706f' }}">
                                                <img width="10" src="https://cdn.givecloud.co/s/assets/icons/trend-{{ $month_supporters_change >= 0 ? 'up-green' : 'down-red' }}.png" alt>
                                                {{ numeral(abs($month_supporters_change))->format('0,0a') }}% Last Year
                                              </div> @else <div style="color:#828282">No Data for Last Year</div> @endif
                                            </div>
                                          </div>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]--> @if ($notable_activity_best_revenue || $notable_activity_best_engagement || $notable_activity_highest_conversion_rate || $notable_activity_best_p2p_fundraiser || $notable_activity_largest_contribution)
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:20px 20px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="tight-box-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="tight-box-outlook" role="presentation" style="width:560px;" width="560" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div class="tight-box" style="background-color: #FFF; border-radius: 10px; margin: 0px auto; max-width: 560px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:20px 25px 10px;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:510px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                              <tr>
                                <td style="vertical-align:top;padding:0;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="left" style="font-size:0px;padding:0 0 10px 0;word-break:break-word;">
                                          <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;"><strong>Notable Activity Yesterday...</strong></div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td align="left" style="font-size:0px;padding:0;word-break:break-word;">
                                          <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Inter, Arial;font-size:14px;line-height:1.4;table-layout:auto;width:100%;border:none;"> @if ($notable_activity_best_revenue) <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr>
                                            <tr>
                                              <td class="vertical-align-top-on-mobile" width="62" style="font-size:0">
                                                <img width="52" height="52" class="52x-thumbnail rounded-full" src="{{ $notable_activity_best_revenue->thumbnail }}" alt style="width: 52px; height: 52px; border-radius: 100%;">
                                              </td>
                                              <td class="collapse-columns-on-mobile">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="table-layout:fixed;">
                                                  <tbody>
                                                    <tr>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <div class="text-base" style="font-size: 14px; padding-right: 20px;">
                                                          <a class="block truncate text-blue no-underline" href="{{ $notable_activity_best_revenue->permalink }}" target="_blank" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f5ef6; text-decoration: none;">{{ $notable_activity_best_revenue->title }}</a>
                                                        </div>
                                                        <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">BEST REVENUE</div>
                                                      </td>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                          <tbody>
                                                            <tr>
                                                              <td width="42%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ numeral($notable_activity_best_revenue->value)->format('0,0') }}</div>
                                                                <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">CONTRIBUTIONS</div>
                                                              </td>
                                                              <td width="58%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ money($notable_activity_best_revenue->revenue)->format('$0,0[.]00') }}</div>
                                                                <div class="text-sm text-slate-400" style="font-size: 11px; color: #848484;">{{ $currency_code }} <strong>REVENUE</strong></div>
                                                              </td>
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr> @endif @if ($notable_activity_best_engagement) <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr>
                                            <tr>
                                              <td class="vertical-align-top-on-mobile" width="62" style="font-size:0">
                                                <img width="52" height="52" class="52x-thumbnail rounded-full" src="{{ $notable_activity_best_engagement->thumbnail }}" alt style="width: 52px; height: 52px; border-radius: 100%;">
                                              </td>
                                              <td class="collapse-columns-on-mobile">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="table-layout:fixed;">
                                                  <tbody>
                                                    <tr>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <div class="text-base" style="font-size: 14px; padding-right: 20px;">
                                                          <a class="block truncate text-blue no-underline" href="{{ $notable_activity_best_engagement->permalink }}" target="_blank" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f5ef6; text-decoration: none;">{{ $notable_activity_best_engagement->title }}</a>
                                                        </div>
                                                        <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">BEST ENGAGEMENT</div>
                                                      </td>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                          <tbody>
                                                            <tr>
                                                              <td width="42%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ numeral($notable_activity_best_engagement->value)->format('0,0') }}%</div>
                                                                <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">ENGAGEMENT</div>
                                                              </td>
                                                              <td width="58%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ money($notable_activity_best_engagement->revenue)->format('$0,0[.]00') }}</div>
                                                                <div class="text-sm text-slate-400" style="font-size: 11px; color: #848484;">{{ $currency_code }} <strong>REVENUE</strong></div>
                                                              </td>
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr> @endif @if ($notable_activity_highest_conversion_rate) <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr>
                                            <tr>
                                              <td class="vertical-align-top-on-mobile" width="62" style="font-size:0">
                                                <img width="52" height="52" class="52x-thumbnail rounded-full" src="{{ $notable_activity_highest_conversion_rate->thumbnail }}" alt style="width: 52px; height: 52px; border-radius: 100%;">
                                              </td>
                                              <td class="collapse-columns-on-mobile">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="table-layout:fixed;">
                                                  <tbody>
                                                    <tr>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <div class="text-base" style="font-size: 14px; padding-right: 20px;">
                                                          <a class="block truncate text-blue no-underline" href="{{ $notable_activity_highest_conversion_rate->permalink }}" target="_blank" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f5ef6; text-decoration: none;">{{ $notable_activity_highest_conversion_rate->title }}</a>
                                                        </div>
                                                        <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">HIGHEST CONVERSION RATE</div>
                                                      </td>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                          <tbody>
                                                            <tr>
                                                              <td width="42%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ numeral($notable_activity_highest_conversion_rate->value)->format('0,0') }}%</div>
                                                                <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">CONVERSION</div>
                                                              </td>
                                                              <td width="58%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ money($notable_activity_highest_conversion_rate->revenue)->format('$0,0[.]00') }}</div>
                                                                <div class="text-sm text-slate-400" style="font-size: 11px; color: #848484;">{{ $currency_code }} <strong>REVENUE</strong></div>
                                                              </td>
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr> @endif @if ($notable_activity_best_p2p_fundraiser) <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr>
                                            <tr>
                                              <td class="vertical-align-top-on-mobile" width="62" style="font-size:0">
                                                <img width="52" height="52" class="52x-thumbnail rounded-full" src="{{ $notable_activity_best_p2p_fundraiser->thumbnail }}" alt style="width: 52px; height: 52px; border-radius: 100%;">
                                              </td>
                                              <td class="collapse-columns-on-mobile">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="table-layout:fixed;">
                                                  <tbody>
                                                    <tr>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <div class="text-base" style="font-size: 14px; padding-right: 20px;">
                                                          <a class="block truncate text-blue no-underline" href="{{ $notable_activity_best_p2p_fundraiser->permalink }}" target="_blank" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f5ef6; text-decoration: none;">{{ $notable_activity_best_p2p_fundraiser->title }}</a>
                                                        </div>
                                                        <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">BEST P2P FUNDRAISER</div>
                                                      </td>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                          <tbody>
                                                            <tr>
                                                              <td width="42%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ numeral($notable_activity_best_p2p_fundraiser->value)->format('0,0') }}</div>
                                                                <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">CONTRIBUTIONS</div>
                                                              </td>
                                                              <td width="58%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ money($notable_activity_best_p2p_fundraiser->revenue)->format('$0,0[.]00') }}</div>
                                                                <div class="text-sm text-slate-400" style="font-size: 11px; color: #848484;">{{ $currency_code }} <strong>REVENUE</strong></div>
                                                              </td>
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr> @endif @if ($notable_activity_largest_contribution) <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr>
                                            <tr>
                                              <td class="vertical-align-top-on-mobile" width="62" style="font-size:0">
                                                <img width="52" height="52" class="52x-thumbnail rounded-full" src="{{ $notable_activity_largest_contribution->thumbnail }}" alt style="width: 52px; height: 52px; border-radius: 100%;">
                                              </td>
                                              <td class="collapse-columns-on-mobile">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="table-layout:fixed;">
                                                  <tbody>
                                                    <tr>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <div class="text-base" style="font-size: 14px; padding-right: 20px;">
                                                          <a class="block truncate text-blue no-underline" href="{{ $notable_activity_largest_contribution->permalink }}" target="_blank" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #1f5ef6; text-decoration: none;">{{ $notable_activity_largest_contribution->title }}</a>
                                                        </div>
                                                        <div class="text-sm font-bold text-slate-400" style="font-size: 11px; color: #848484; font-weight: bold;">LARGEST CONTRIBUTION</div>
                                                      </td>
                                                      <td width="50%" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                          <tbody>
                                                            <tr>
                                                              <td width="100%" align="right" valign="middle" style="padding:0;word-break:break-word;">
                                                                <div class="text-base" style="font-size: 14px;">{{ money($notable_activity_largest_contribution->revenue)->format('$0,0[.]00') }}</div>
                                                                <div class="text-sm text-slate-400" style="font-size: 11px; color: #848484;">{{ $currency_code }} <strong>REVENUE</strong></div>
                                                              </td>
                                                            </tr>
                                                          </tbody>
                                                        </table>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td colspan="3" height="10" style="margin:0;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr> @endif
                                          </table>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]--> @endif @if (count($keep_your_eyes_on))
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:20px 20px 0;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="tight-box-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="tight-box-outlook" role="presentation" style="width:560px;" width="560" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div class="tight-box" style="background-color: #FFF; border-radius: 10px; margin: 0px auto; max-width: 560px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:20px 25px;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:510px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                              <tr>
                                <td style="vertical-align:top;padding:0;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style width="100%">
                                    <tbody>
                                      <tr>
                                        <td align="left" style="font-size:0px;padding:0 0 20px 0;word-break:break-word;">
                                          <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;"><strong>Keep Your Eyes On...</strong></div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td align="left" class="collapse-columns-on-mobile" style="font-size:0px;padding:0;word-break:break-word;">
                                          <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Inter, Arial;font-size:14px;line-height:1.4;table-layout:auto;width:100%;border:none;">
                                            <tr> @foreach ($keep_your_eyes_on as $index => $item) @php if ($loop->index && $loop->index % 2 === 0) echo '</tr>
                                            <tr>' @endphp <td width="50%" style="padding:4px 0">
                                                <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                  <tbody>
                                                    <tr>
                                                      <td width="72" align="left" valign="middle" style="padding:0;word-break:break-word;">
                                                        <div class="52x-thumbnail" style="width: 52px; height: 52px; border-radius: 100%; background-color: {{ $item->icon_background_colour }}; color: {{ $item->icon_colour }}; text-align: center;"> @if ($item->icon_image_url) <img width="52" height="52" src="{{ $item->icon_image_url }}" alt> @endif </div>
                                                      </td>
                                                      <td align="left" valign="middle" class="keep-your-eyes-on--content" style="padding: 0; font-size: 14px; word-break: break-word;">
                                                        <a class="text-blue no-underline" href="{{ $item->permalink }}" target="_blank" style="color: #1f5ef6; text-decoration: none;">{{ $item->content }}</a>
                                                      </td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </td> @endforeach </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]--> @endif
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:0 10px 40px;text-align:center;">
              <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="spacer-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="spacer-outlook" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div class="spacer" style="padding: 0px; height: 20px; margin: 0px auto; max-width: 580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr><![endif]--> @if ($appFacebookUrl || $appInstagramUrl || $appLinkedinUrl || $appTwitterUrl)
              <!--[if mso | IE]><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div style="margin:0px auto;max-width:580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:0 10px;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="tight-box-outlook social-icons-outlook" style="width:560px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix tight-box social-icons" style="background-color: #FFF; border-radius: 10px; padding: 10px 0; font-size: 0; line-height: 0; text-align: left; display: inline-block; width: 100%; direction: ltr;">
                          <!--[if mso | IE]><table border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td style="vertical-align:top;width:93px;" ><![endif]-->
                          <div class="mj-column-per-16-666666666666668 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:16%;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                              <tbody>
                              </tbody>
                            </table>
                          </div>
                          <!--[if mso | IE]></td><![endif]--> @if ($appFacebookUrl)
                          <!--[if mso | IE]><td style="vertical-align:top;width:93px;" ><![endif]-->
                          <div class="mj-column-per-16-666666666666668 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:16%;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                              <tbody>
                                <tr>
                                  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                      <tbody>
                                        <tr>
                                          <td style="width:20px;">
                                            <a href="{{ $appFacebookUrl }}" target="_blank" title="Check us out on Facebook">
                                              <img height="20" src="https://cdn.givecloud.co/static/notifications/transactional-email/facebook-large-dark.png" style="border:0;display:block;outline:none;text-decoration:none;height:20px;width:100%;font-size:14px;" title="Check us out on Facebook" width="20">
                                            </a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <!--[if mso | IE]></td><![endif]--> @endif @if ($appInstagramUrl)
                          <!--[if mso | IE]><td style="vertical-align:top;width:93px;" ><![endif]-->
                          <div class="mj-column-per-16-666666666666668 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:16%;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                              <tbody>
                                <tr>
                                  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                      <tbody>
                                        <tr>
                                          <td style="width:21px;">
                                            <a href="{{ $appInstagramUrl }}" target="_blank">
                                              <img height="20" src="https://cdn.givecloud.co/static/notifications/transactional-email/instagram-large-dark.png" style="border:0;display:block;outline:none;text-decoration:none;height:20px;width:100%;font-size:14px;" width="21">
                                            </a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <!--[if mso | IE]></td><![endif]--> @endif @if ($appLinkedinUrl)
                          <!--[if mso | IE]><td style="vertical-align:top;width:93px;" ><![endif]-->
                          <div class="mj-column-per-16-666666666666668 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:16%;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                              <tbody>
                                <tr>
                                  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                      <tbody>
                                        <tr>
                                          <td style="width:20px;">
                                            <a href="{{ $appLinkedinUrl }}" target="_blank">
                                              <img height="20" src="https://cdn.givecloud.co/static/notifications/transactional-email/linkedin-large-dark.png" style="border:0;display:block;outline:none;text-decoration:none;height:20px;width:100%;font-size:14px;" width="20">
                                            </a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <!--[if mso | IE]></td><![endif]--> @endif @if ($appTwitterUrl)
                          <!--[if mso | IE]><td style="vertical-align:top;width:93px;" ><![endif]-->
                          <div class="mj-column-per-16-666666666666668 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:16%;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                              <tbody>
                                <tr>
                                  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                      <tbody>
                                        <tr>
                                          <td style="width:21px;">
                                            <a href="{{ $appTwitterUrl }}" target="_blank">
                                              <img height="18" src="https://cdn.givecloud.co/static/notifications/transactional-email/twitter-large-dark.png" style="border:0;display:block;outline:none;text-decoration:none;height:18px;width:100%;font-size:14px;" width="21">
                                            </a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <!--[if mso | IE]></td><![endif]--> @endif
                          <!--[if mso | IE]><td style="vertical-align:top;width:93px;" ><![endif]-->
                          <div class="mj-column-per-16-666666666666668 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:16%;">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                              <tbody>
                              </tbody>
                            </table>
                          </div>
                          <!--[if mso | IE]></td></tr></table><![endif]-->
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr><![endif]--> @endif
              <!--[if mso | IE]><tr><td class="" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div style="margin:0px auto;max-width:580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:580px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                              <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                  <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:center;color:#979797;">
                                    <p>{{ $appOrgName }}<br>{{ $appOrgLocation }}</p>
                                  </div>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr></table><![endif]-->
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!--[if mso | IE]></td></tr></table><![endif]-->
  </div>
</body>

</html>
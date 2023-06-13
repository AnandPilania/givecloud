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
  </style>
</head>

<body style="word-spacing:normal;background-color:#F4F4F4;">
  <div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;"> Givecloud got an error trying to send data to you through the order_completed webhook. </div>
  <div style="background-color:#F4F4F4;">
    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
    <div style="margin:0px auto;max-width:600px;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
        <tbody>
          <tr>
            <td style="direction:ltr;font-size:0px;padding:40px 10px;text-align:center;">
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
              <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="box-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="box-outlook" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div class="box" style="background-color: #FFF; border-radius: 10px; padding: 10px 20px; margin: 0px auto; max-width: 580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:580px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                              <tr>
                                <td style="font-size:0px;padding:25px;padding-bottom:0;word-break:break-word;">
                                  <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:#000000;font-family:Inter, Arial;font-size:14px;line-height:1.4;table-layout:auto;width:100%;border:none;">
                                    <tr>
                                      <td style="vertical-align:middle" width="95">
                                        <img src="https://cdn.givecloud.co/static/notifications/fa-dark-question-circle.png" alt style="width:70px;height:auto">
                                      </td>
                                      <td style="vertical-align:middle">
                                        <mj-text style="font-family:Inter, Arial;font-size:14px;line-height:1.4;color:#000000;">
                                          <h1>Webhook Failed</h1>
                                        </mj-text>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;padding-top:0;word-break:break-word;">
                                  <div style="font-family:Inter, Arial;font-size:18px;font-weight:300;line-height:1.4;text-align:left;color:#474747;">
                                    <p>Givecloud got an error trying to send data to you through the order_completed webhook. Please forward this message to your developer and review your logs for more detail.</p>
                                  </div>
                                </td>
                              </tr>
                              <tr>
                                <td align="center" vertical-align="middle" style="font-size:0px;padding:20px;word-break:break-word;">
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;line-height:100%;">
                                    <tbody>
                                      <tr>
                                        <td align="center" bgcolor="#fc58af" role="presentation" style="border:none;border-radius:30px;cursor:auto;mso-padding-alt:10px 25px;background:#fc58af;" valign="middle">
                                          <a href="{{ secure_site_url('jpanel/settings/hooks') }}" style="display:inline-block;background:#fc58af;color:white;font-family:Inter, Arial;font-size:16px;font-weight:500;line-height:1.4;margin:0;text-decoration:none;text-transform:none;padding:10px 25px;mso-padding-alt:0px;border-radius:30px;" target="_blank"> View Webhook Logs </a>
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
              <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="spacer-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="spacer-outlook" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
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
              <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="box-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="box-outlook" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
              <div class="box" style="background-color: #FFF; border-radius: 10px; padding: 10px 20px; margin: 0px auto; max-width: 580px;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                  <tbody>
                    <tr>
                      <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                        <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:580px;" ><![endif]-->
                        <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>
                              <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;padding-bottom:0;word-break:break-word;">
                                  <div style="font-family:Inter, Arial;font-size:14px;line-height:1.4;text-align:left;color:#000000;">
                                    <h2>Error Message</h2>
                                  </div>
                                </td>
                              </tr>
                              <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;padding-top:0;padding-bottom:0;word-break:break-word;">
                                  <div style="font-family:Inter, Arial;font-size:16px;font-weight:300;line-height:1.4;text-align:left;color:#000000;">{{ $error_message }}</div>
                                </td>
                              </tr> @if ($hook_delivery) <tr>
                                <td align="left" style="font-size:0px;padding:10px 25px;padding-top:0;word-break:break-word;">
                                  <div style="font-family:Inter, Arial;font-size:14px;font-weight:300;line-height:1.4;text-align:left;color:#474747;">Delivery {{ $hook_delivery->guid }}</div>
                                </td>
                              </tr> @endif
                            </tbody>
                          </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!--[if mso | IE]></td></tr></table></td></tr><tr><td class="spacer-outlook" width="600px" ><table align="center" border="0" cellpadding="0" cellspacing="0" class="spacer-outlook" role="presentation" style="width:580px;" width="580" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
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
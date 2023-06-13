<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>{{ trans('auth.transparent_title') }}</title>

  <style>
    .spinner {
      display: inline-block;
      position: absolute;
      top: 50%;
      left: 50%;
      width: 160px;
      height: 160px;
      margin-top: -80px;
      margin-left: -80px;
    }

    .spinner div {
      box-sizing: border-box;
      display: block;
      position: absolute;
      width: 128px;
      height: 128px;
      margin: 8px;
      border: 8px solid #333;
      border-radius: 50%;
      animation: spinner 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
      border-color: #333 transparent transparent transparent;
    }

    .spinner div:nth-child(1) { animation-delay: -0.45s; }
    .spinner div:nth-child(2) { animation-delay: -0.3s; }
    .spinner div:nth-child(3) { animation-delay: -0.15s; }

    @keyframes spinner {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>

<div class="spinner">
  <div></div>
  <div></div>
  <div></div>
  <div></div>
</div>

<script>
if (window.opener) {
  window.opener.postMessage(@json($message), @json($origin));
  window.opener.focus()

  setTimeout(function() {
    window.close();
  }, 300);
}
</script>

</body>
</html>

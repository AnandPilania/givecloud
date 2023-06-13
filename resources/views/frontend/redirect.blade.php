<!DOCTYPE html>
<html lang="en">
<head>
    <title>Redirecting...</title>
    <meta http-equiv="refresh" content="0;url={{ $url }}" />
</head>
<body>

If you are not redirected within 5 seconds,
please <a href="{{ $url }}">click here</a>.

<script>
setTimeout(function() {
    window.location = @json($url);
}, 250);
</script>

</body>
</html>

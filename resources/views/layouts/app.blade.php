<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>@yield('title', '') - {{ config('app.name') }}</title>
	@vite(['resources/js/app.js', 'resources/scss/app.scss'])
</head>

<body>
	@include('components.guest.navbar')

	@yield('content')

	@include('components.guest.footer')

	<x-toast />

	@stack('scripts')
	<script>
		if (typeof AOS !== 'undefined' && AOS && typeof AOS.init === 'function') {
			AOS.init();
		}
	</script>
</body>

</html>

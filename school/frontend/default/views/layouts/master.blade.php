<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="owner" content="inilabs">
    <meta name="email" content="info@inilabs.net">
    <meta name="portfolio" content="https://codecanyon.net/user/inilabs/portfolio">
    <title> {{ frontendData::get_backend('sname') }} </title>
    <link rel="icon" href="{{ base_url('uploads/images/' . frontendData::get_backend('photo')) }}">
    @include('views/partials/head')

</head>

<body>
    @include('views/partials/navbar')
    @yield('content')
    @include('views/partials/footer')
    @include('views/partials/script')
</body>

</html>

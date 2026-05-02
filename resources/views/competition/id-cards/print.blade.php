<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document['title'] }}</title>
    @include('competition.id-cards.partials.styles', ['document' => $document, 'renderMode' => 'preview'])
</head>
<body>
    @include('competition.id-cards.partials.deck', ['document' => $document])
</body>
</html>

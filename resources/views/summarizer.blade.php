<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Summarizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Text Summarizer</h1>
        <form action="{{ route('summarize') }}" method="POST" class="mt-4">
            @csrf
            <div class="form-group">
                <label for="text">Masukkan Teks:</label>
                <textarea name="text" id="text" rows="5" class="form-control">{{ $text ?? '' }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Ringkas Teks</button>
        </form>

        @if(isset($summary))
            <h3 class="mt-5">Ringkasan:</h3>
            <p>{{ $summary }}</p>
        @endif
    </div>
</body>
</html>

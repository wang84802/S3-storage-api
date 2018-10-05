<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laravel Cloud File Upload</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">

    <h2>Laravel Cloud Upload Documents</h2>
{{--<p><a href="{{ route('documents.create') }}">Create Document</a></p>--}}

    <form action="{{ url('documents') }}" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="exampleInputFile">File input</label>
            <input type="file" name="profile_image" id="exampleInputFile">
        </div>
        {{ csrf_field() }}
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>


    @foreach ($documents as $document)

        {{--<ul id="document-{{ $document->id }}">
            <li>ID: {{ $document->id }}</li>
            <li>Name: <a href="{{ $document->url }}">{{ $document->name }}</a></li>
            <li class="preview-url">
                @if ($document->preview_url)
                    <a href="{{ $document->preview_url }}">Preview</a>
                @else
                    No Preview
                @endif
            </li>
        </ul>--}}
    @endforeach

</div>
</body>

</html>
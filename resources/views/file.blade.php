<link href="{{ asset('css/app.css') }}" rel="stylesheet" />
<div class="container">
    @foreach ($files as $file)
        {{ $file->name }}.{{ $file->extension }}
        {{ $file->size }}
    @endforeach
</div>
{{ $files->render('vendor.pagination.simple-bootstrap-4') }}

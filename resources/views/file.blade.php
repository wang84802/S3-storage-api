<div class="container">
    @foreach ($Files as $file)
        {{ $file->name }}
    @endforeach
</div>

{{ $Files->appends(['sort' => 'votes'])->render() }}
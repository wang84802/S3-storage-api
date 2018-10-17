<form action="/flush" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    <button>flush</button>
</form>
<form action="/upload" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    {{--Product name:
    <br />
    <input type="text" name="name" />--}}
    <br /><br />
    Product photos (can attach more than one):
    <br />
    <input type="file" name="photos[]" multiple />
    <br /><br />
    <input type="submit" value="Upload" />
    <br />
    @if (session()->has('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif


</form>
<form action="{{ url('/download') }}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    請輸入文字：<input type="text" name="file_name">
    <br>
    <button type="submit" class="btn btn-default">Submit</button>
</form>
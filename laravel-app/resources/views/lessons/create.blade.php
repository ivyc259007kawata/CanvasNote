<h1>教材作成</h1>

<form action="{{ route('lessons.store') }}" method="POST">
    @csrf

    <div>
        <label>教材名</label><br>
        <input type="text" name="title">
    </div>

    <br>

    <div>
        <label>説明</label><br>
        <textarea name="description"></textarea>
    </div>

    <br>

    <div>
        <label>
            <input type="checkbox" name="is_public">
            公開する
        </label>
    </div>

    <br>

    <button type="submit">
        保存
    </button>
</form>
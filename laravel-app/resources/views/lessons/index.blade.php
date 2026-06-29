<h1>教材一覧</h1>

<a href="{{ route('lessons.create') }}">
    新規教材作成
</a>

<hr>

@foreach($lessons as $lesson)
    <div>
        <h3>
            <a href="{{ route('lessons.show', $lesson) }}">
                {{ $lesson->title }}
            </a>
        </h3>

        <p>{{ $lesson->description }}</p>

        <p>
            公開状態：
            {{ $lesson->is_public ? '公開' : '非公開' }}
        </p>
    </div>

    <hr>
@endforeach
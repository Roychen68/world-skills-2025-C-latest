<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta property="og:title" content="{{ $meta['title'] ?? "" }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $meta['cover'] ?? "" }}">
    <meta type="twitter:card" property="og:card">
    <meta property="og:url" content="{{ url()->current() }}">
    <title>Module C</title>
    <link rel="stylesheet" href="{{ asset("resources/css/app.css") }}">
    <link rel="stylesheet" href="{{ asset("resources/css/bootstrap.css") }}">
    <style>
        h1.legend {
            line-height: 50px;
        }

        div.content p:first-child:first-letter {
            float: left;
            font-size: 3rem;
        }
    </style>
</head>
<body class="overflow-auto">
<div class="cover">
    <div class="cover-effect"></div>
    <img src="{{ asset("public/content-pages/images/".($meta['cover'] ?? ""))}}" alt="{{$meta['cover'] ?? ""}}"
         class="cover-img">
</div>
<h1 class="legend"><b>{{ $meta['title'] ?? "" }}</b></h1>
<div class="w-75 d-flex mb-5" style="margin: 100px 0 0 auto">
    <div class="col-8">
        <div class="card w-100 p-2 rounded-0 content">
            {!! $body ?? "" !!}
        </div>
        <div class="card w-100 p-2 rounded-0 content">
            <ul>
                @foreach($articles as $article)
                    @if($article != null)
                        <li>
                            <a href="{{ asset("/heritages/".str_replace([".txt",".html"],[""],$article['path'])) }}">{{$article['title']}}</a>
                            <p>{{ $article['meta']['summary'] ?? "" }}</p>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-4">
        <div class="card w-100 p-2 rounded-0 top-0 position-sticky align-content-start">
            <p>Date: {{ $date ?? "" }}</p>
            <p>Tags: @foreach($meta['tags'] ?? [] as $tag)
                    <a href="{{url("tags/".Str::lower(trim($tag)))}}">{{$tag}}</a>
                @endforeach</p>
            <p>Draft: {{ $meta['draft'] ?? "" }}</p>
            <form action="{{ url("like") }}" method="get">
                <input type="hidden" name="path" value="{{ $path ?? "" }}">
                @if(in_array($path ?? "",session("likes",[])))
                    <button class="btn btn-outline-danger d-inline-block">Dislike</button>
                @else
                    <button class="btn btn-outline-primary d-inline-block">Like</button>
                @endif
            </form>
        </div>
    </div>
    <div class="modal fade">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body">
                    <img src="" alt="" id="img" class="w-100">
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<script src="{{ asset("resources/js/jquery-3.7.1.js") }}"></script>
<script src="{{ asset("resources/js/bootstrap.js") }}"></script>
<script>
    $("div.cover-effect").on("mousemove", function (e) {
        let rect = this.getBoundingClientRect()
        $(this).css({
            "--x": e.clientX - rect.left + "px",
            "--y": e.clientY - rect.top + "px",
        })
    })

    function modal(e) {
        $("#img").attr("src", e.src)
        $("div.modal").modal("show")
    }

    $(document).on("scroll", function () {
        $("div.modal").modal("hide")
    })
    $(document).on("click", function () {
        $("div.modal").modal("hide")
    })
</script>

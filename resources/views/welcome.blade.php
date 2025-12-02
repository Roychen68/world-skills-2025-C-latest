<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Module C</title>
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="stylesheet" href="{{ asset("resources/css/app.css") }}">
    <link rel="stylesheet" href="{{ asset("resources/css/bootstrap.css") }}">
</head>
<body>
<div class="cover"></div>
<h1 class="legend"><b>Listing Page Layout</b></h1>
<div id="app">
    <div class="w-75 d-flex mb-5" style="margin: 100px 0 0 auto">
        <div class="col-8">
            <div class="card w-100 p-2 rounded-0 align-content-start">
                <ul v-if="mode == 'view'">
                    @foreach($folders as $folder)
                        <li>
                            <a href="{{ asset("/heritages/$base/$folder") }}">{{$folder}}</a>
                        </li>
                    @endforeach
{{--                    @dd($files)--}}
                    @foreach($files as $file)
                        @if($file != null)
                            <li>
                                <a href="{{ asset("/heritages/".str_replace([".txt",".html"],[""],$file['path'])) }}">
                                    @php echo $file['title'] @endphp
                                </a>
                                <p>{{ $file['meta']['summary'] ?? "" }}</p>
                            </li>
                        @endif
                    @endforeach
                </ul>
                <ul v-if="mode == 'search'">
                    <li v-for="result in results">
                        <a :href="'{{ asset("heritages/") }}'+'/'+result.path">@{{result['title']}}</a>
                        <p>@{{ result['meta']['summary'] ?? "" }}</p>
                    </li>
                </ul>
            </div>
            @if(url('') == url()->current())
                <div class="card w-100 mt-3 p-2 rounded-0">
                    <ul>
                        @foreach($histories as $history)
                            @if($history != null)
                                <li>
                                    <a href="{{ asset("/heritages/".str_replace([".txt",".html"],[""],$history['path'])) }}">{{$history['title']}}</a>
                                    <p>{{ $history['meta']['summary'] ?? "" }}</p>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="col-4">
            <form class="card w-100 p-2 rounded-0 top-0 position-sticky align-content-start" @submit.prevent="search()">
                <label for="search">Search</label>
                <div class="d-flex mt-4">
                    <input type="text" placeholder="KEYWORD" style="margin-right: 10px" v-model="keyword" name="tag"
                           required>
                    <input type="submit" value="SEARCH">
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<script src="{{ asset("resources/js/jquery-3.7.1.js") }}"></script>
<script src="{{ asset("resources/js/bootstrap.js") }}"></script>
<script src="{{ asset("resources/js/vue.3.5.16.js") }}"></script>
<script>
    const {createApp} = Vue
    createApp({
        data() {
            return {
                mode: "view",
                keyword: "",
                results: []
            }
        },
        methods: {
            search() {
                this.mode = "search"
                $.get("{{ url("search") }}", {tag: this.keyword}, (res) => {
                    this.results = res.files
                    console.log(this.results)
                })
            }
        }
    }).mount("#app")
</script>

<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class path extends Controller
{
    /**
     * @param $page
     * @return array|null
     */


    public function parseFile($page = null)
    {
        $path = public_path("content-pages/" . $page);
        if (!preg_match("/^(\d{4}-\d{2}-\d{2})-(.*)+/", File::name($path), $m)) return null;
        $date = $m[1];
        $title = Str::ucfirst(str_replace("-", " ", $m[2]));
//        "Lyon place bellecour"
//        dd($path    );
        $raw = File::get($path);
        $parts = explode("---", $raw, 3);
        $meta = [];
        foreach (preg_split("/\n/", trim($parts[1])) as $line) {
            $kv = explode(":", $line, 2);
            $meta[trim($kv[0])] = trim($kv[1]);
        }
        if ($date > date("Y-m-d")) return null;
        if (isset($meta['draft']) && Str::lower($meta['draft']) == 'true') return null; else $meta['draft'] = "false";
        if (!isset($meta['title'])) {
            if (preg_match("/<h1>(.*)<\/h1>/s", $raw, $d)) {
                $meta['title'] = trim($d[1]);
            } else {
                $meta['title'] = $title;
            }
        }
        if (!isset($meta['cover'])) {
            if (File::isFile(public_path("content-pages/images/$m[0].jpg"))) {
                $meta['cover'] = $m[0] . ".jpg";
            } elseif (File::isFile(public_path("content-pages/images/$m[0].jpeg"))) {
                $meta['cover'] = $m[0] . ".jpeg";
            }
        }
        $body = "";
        foreach (preg_split("/\n/", trim($parts[2])) as $line) {
            if (str_ends_with($page, ".txt")) {
                if ($line == "" || $line == null) {
                    continue;
                } elseif (preg_match("/^(.+).(jpg|jpeg|png)/", $line)) {
                    $body .= "<img src='" . asset("public/content-pages/images/$line") . "' alt='$line' class='w-100' onclick='modal(this)'>";
                } else {
                    $body .= "<p>$line</p>";
                }
            } else {
                if ($line == "" || $line == null) {
                    continue;
                } elseif (preg_match('/<img\s+src="(.*)"/', $line, $image)) {
//                    dd($image);
                    $body .= "<img src='".asset("public/content-pages/images/$image[1]")."' onclick='modal(this)'>";
                } else {
                    $body .= $line;
                }

            }
        }
        $tags = isset($meta['tags']) ? explode(",", $meta['tags']) : [];
        $meta['tags'] = array_map(fn($f) => trim($f), $tags);
//        dd($meta['tags']);
        return ["date" => $date, "title" => $meta['title'], "path" => $page, "meta" => $meta, "body" => $body,];
    }

    /**
     * @param $path
     * @return Application|Factory|View
     */
    public function listen($path = null)
    {
        $p = $path ? urldecode($path) : $path;
        $AbsolutePath = public_path("content-pages" . ($p ? "/$p" : ""));
        if (!File::isFile($AbsolutePath)) {
            if (File::isFile($AbsolutePath . ".html")) {
                $p .= ".html";
            } elseif (File::isFile($AbsolutePath . ".txt")) {
                $p .= ".txt";
            }
        }
        $histories = session("histories", []);
        $file = File::isFile($AbsolutePath) ? $AbsolutePath : public_path("content-pages/" . $p);
        if (File::isFile($file)) {
            $content = $this->parseFile($p);
            $histories = array_filter($histories, fn($his) => $his !== ($content['path'] ?? ''));
            array_unshift($histories, ($content['path'] ?? ""));
            session(["histories" => $histories]);
            $articles = $this->article(($content['meta']['tags'] ?? []), ($content['path'] ?? ""));
            return view("info", ($content ?? []), ["articles" => ($articles ?? [])]);
        }
        $dir = is_dir($AbsolutePath) ? $AbsolutePath : public_path("content-pages");
        $prefix = $p ? ltrim($p, "/") . "/" : "";
        $folders = array_map("basename", array_filter(File::directories($dir), fn($f) => !str_contains($f, "image")));
        $files = [];
        $HistoriesLayout = [];
        foreach ($histories as $history) {
            $path = $this->checkPath($history);
            $HistoriesLayout[] = $this->parseFile($path);
        }
        foreach (File::files($dir) as $file) {
            $dn = $file->getRelativePathname();
            $files[] = $this->parseFile($prefix . $dn);
        }
        return view("welcome", ["folders" => $folders, "base" => $prefix, "files" => $files, "histories" => $HistoriesLayout]);
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    function search(Request $request)
    {
        $tags = explode("/", Str::lower($request->input("tag", "")));
        $files = File::allFiles(public_path("content-pages"));
        $layouts = [];
        foreach ($files as $file) {
            $fn = $file->getRelativePathname();
            if (!str_ends_with($fn, ".txt") && !str_ends_with($fn, ".html")) continue;
            $content = $this->parseFile($fn);
            $body = "";
            if (isset($content['body'])) {
                $body = Str::lower($content['body']);
            }
            $title = "";
            if (isset($content['meta']['title'])) {
                $title = Str::lower($content['meta']['title']);
            }
            $seen = [];
            foreach ($tags as $t) {
                if (str_contains($title, Str::lower($t)) || str_contains($body, Str::lower($t))) {
                    if (in_array($content['path'], $seen)) continue;
                    $layouts[] = $content;
                    $seen[] = $content['path'];
                }
            }
        }
        return ["files" => $layouts];
    }

    /**
     * @param $tag
     * @return Application|Factory|View
     */
    public function tag($tag = null)
    {
        $tag = Str::lower($tag);
        $files = File::allFiles(public_path("content-pages"));
        $layouts = [];
        foreach ($files as $file) {
            $fn = $file->getRelativePathname();
            if (!str_ends_with($fn, ".txt") && !str_ends_with($fn, ".html")) continue;
            $content = $this->parseFile($fn);
            $tags = [];
            if (isset($content['meta']['tags'])) {
                $tags = array_map(fn($t) => Str::lower($t), $content['meta']['tags']);
            }
            if (in_array($tag, $tags)) {
                $layouts[] = $content;
            }
        }
        return view("tag", ["files" => $layouts]);
    }

    public function like(Request $file)
    {
        $path = $file->input("path", "");
        $likes = session("likes", []);
        if (in_array($path, $likes)) {
            $key = array_search($path, $likes);
            unset($likes[$key]);
        } else {
            array_unshift($likes, $path);
        }
        session(["likes" => $likes]);
        return redirect()->back();
    }

    public function article($FileTags = null, $path = "")
    {
        $layout = [];
        $seen = [];
        foreach (File::allFiles(public_path("content-pages")) as $file) {
            if (File::basename($path) != File::basename($file)) {
                $dn = $file->getRelativePathname();
                if (!str_ends_with($dn, ".txt") && !str_ends_with($dn, ".html")) continue;
                $content = $this->parseFile($dn);
                $tags = $content['meta']['tags'] ?? [];
                foreach ($FileTags as $fileTag) {
                    if (in_array($fileTag, $tags)) {
                        if (!in_array($content['path'] ?? "", $seen)) {
                            $layout[] = $content;
                            $seen[] = ($content['path'] ?? "");
                        }
                    }
                }
            }
        }
        return $layout;
    }

    public function favorite()
    {
        $histories = session("histories", []);
        $HistoriesLayout = [];
        foreach ($histories as $history) {
            $path = $this->checkPath($history);
            $HistoriesLayout[] = $this->parseFile($path);
        }
        $likes = session("likes", []);
        $LikesLayout = [];
        foreach ($likes as $like) {
            $path = $this->checkPath($like);
            $LikesLayout[] = $this->parseFile($path);
        }
        return view("favorite", ["history" => $HistoriesLayout, "likes" => $LikesLayout]);
    }
}

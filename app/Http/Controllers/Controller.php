<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function checkPath($path = null)
    {
        $p = $path ? urldecode($path) : $path;
        if (!File::isFile(public_path("content-pages/".$p))) {
            if (File::isFile(public_path("content-pages/".$p) . ".html")) {
                $p .= ".html";
            } elseif (File::isFile(public_path("content-pages/".$p) . ".txt")) {
                $p .= ".txt";
            }
        }
        return $p;
    }

    public function arrange($folders,$files)
    {
        uksort($folders,function ($a,$b) {
            return strcmp($a,$b);
        });
        uksort($files,function ($a,$b) {
            return strcmp(File::name($b['path']),File::name($a['path']));
        });
        return ["folders" => $folders , "files" => $files];
    }
}

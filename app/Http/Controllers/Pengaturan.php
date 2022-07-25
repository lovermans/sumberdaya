<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class Pengaturan extends Controller
{
    public function index()
    {
        $HtmlPenuh = view('atur');
        $HtmlIsi = implode('', $HtmlPenuh->renderSections());
        $HtmlHeader = ['Vary' => 'Accept'];
        return request()->pjax() ? response($HtmlIsi)->withHeaders($HtmlHeader) : $HtmlPenuh;
    }
}

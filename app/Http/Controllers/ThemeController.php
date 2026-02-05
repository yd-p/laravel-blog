<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

abstract class ThemeController
{
    /**
     * 渲染主题
     */
    protected function renderView(string $view, array $data = []):View
    {
        if (view()->exists('blog::' . $view)) {
            return view('blog::' . $view, $data);
        }
        return view('blog::' . $view, $data);
    }
}

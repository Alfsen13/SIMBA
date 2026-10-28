<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController
{
     
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', array_keys(config('locales.supported', [])))],
        ]);

        $request->session()->put('locale', $data['locale']);
        app()->setLocale($data['locale']);

        return back();
    }
}


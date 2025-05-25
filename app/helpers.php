<?php

if (!function_exists('localized_route')) {
    function localized_route($name, $parameters = [], $absolute = true)
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        $locale = app()->getLocale();
        $defaultLocale = config('app.locale_def');

        // Добавляем только если локаль не по умолчанию
        if ($locale !== $defaultLocale && in_array($locale, config('app.locales'))) {
            $parameters = array_merge(['locale' => $locale], is_array($parameters) ? $parameters : [$parameters]);
        }

        return rtrim(route($name, $parameters, $absolute), '/') . '/';
    }
}

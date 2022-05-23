<?php

namespace Moloni\Enums;

class MoloniRoutes
{
    public const LOGIN = 'moloni_es_login_home';
    public const LOGIN_SUBMIT = 'moloni_es_login_submit';
    public const LOGIN_RETRIEVE_CODE = 'moloni_es_login_retrievecode';
    public const LOGIN_COMPANY_SELECT = 'moloni_es_login_company_select';
    public const LOGIN_COMPANY_SUBMIT = 'moloni_es_login_company_submit';

    public const ORDERS = 'moloni_es_orders_home';
    public const ORDERS_CREATE = 'moloni_es_orders_create';
    public const ORDERS_DISCARD = 'moloni_es_orders_discard';

    public const DOCUMENTS = 'moloni_es_documents_home';
    public const DOCUMENTS_DOWNLOAD = 'moloni_es_documents_download';
    public const DOCUMENTS_RESTORE = 'moloni_es_documents_restore';

    public const TOOLS = 'moloni_es_tools_home';
    public const TOOLS_IMPORT_PRODUCTS = 'moloni_es_tools_import_products';
    public const TOOLS_IMPORT_CATEGORIES = 'moloni_es_tools_import_categories';
    public const TOOLS_DISCARD_ORDERS = 'moloni_es_tools_discard_orders';
    public const TOOLS_OPEN_LOGS = 'moloni_es_tools_open_logs';
    public const TOOLS_DELETE_LOGS = 'moloni_es_tools_delete_logs';
    public const TOOLS_LOGOUT = 'moloni_es_tools_logout';
    public const TOOLS_EXPORT = 'moloni_es_tools_export';

    public const SETTINGS = 'moloni_es_settings_home';
    public const SETTINGS_SAVE = 'moloni_es_settings_save';

    public const ROUTES_FULLY_AUTHENTICATED = [
        self::ORDERS,
        self::ORDERS_CREATE,
        self::ORDERS_DISCARD,
        self::DOCUMENTS,
        self::DOCUMENTS_DOWNLOAD,
        self::DOCUMENTS_RESTORE,
        self::TOOLS,
        self::TOOLS_IMPORT_PRODUCTS,
        self::TOOLS_IMPORT_CATEGORIES,
        self::TOOLS_DISCARD_ORDERS,
        self::TOOLS_OPEN_LOGS,
        self::TOOLS_DELETE_LOGS,
        self::TOOLS_LOGOUT,
        self::TOOLS_EXPORT,
        self::SETTINGS,
        self::SETTINGS_SAVE,
    ];

    public const ROUTES_PARTIALLY_AUTHENTICATED = [
        self::LOGIN_COMPANY_SELECT,
        self::LOGIN_COMPANY_SUBMIT,
        self::LOGIN_RETRIEVE_CODE,
    ];

    public const ROUTES_NON_AUTHENTICATED = [
        self::LOGIN,
        self::LOGIN_SUBMIT,
    ];

    public static function isFullyAuthenticatedRoute(string $route): bool
    {
        return in_array($route, self::ROUTES_FULLY_AUTHENTICATED, true);
    }

    public static function isPartiallyAuthenticatedRoute(string $route): bool
    {
        return in_array($route, self::ROUTES_PARTIALLY_AUTHENTICATED, true);
    }

    public static function isNonAuthenticatedRoute(string $route): bool
    {
        return in_array($route, self::ROUTES_NON_AUTHENTICATED, true);
    }
}

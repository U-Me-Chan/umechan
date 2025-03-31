<?php

namespace Ridouchire\RadioMetrics\Utils\Phpstan;

class EnvironmentMixin
{
    public string $radio_log_level;
    public string $mysql_hostname;
    public string $mysql_database;
    public string $mysql_username;
    public string $mysql_password;
    public string $radio_api_url;
    public string $mpd_hostname;
    public int $mpd_port;
    public string $mpd_database_path;
}

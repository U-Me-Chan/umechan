<?php

require_once __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;
use Medoo\Medoo;
use Ridouchire\RadioMetrics\DataCollector;
use Ridouchire\RadioMetrics\Storage\Repository;
use Ridouchire\RadioMetrics\Storage\Artist\Artist;
use Ridouchire\RadioMetrics\Storage\Track\Track;
use Ridouchire\RadioMetrics\Storage\Playlist\Playlist;
use Ridouchire\RadioMetrics\Storage\Record\Record;

define('ESTIMATE_VALUE_LISTENERS_NOT_CHANGE', 1);
define('ESTIMATE_VALUE_LISTENERS_INCREASE', 100);
define('ESTIMATE_VALUE_LISTENERS_DECREASE', 100);
define('PLAYING_PERIOD', 10 * 60); // 10 минут

$db = new Medoo([
    'database_type' => 'mysql',
    'database_name' => $_ENV['MYSQL_DATABASE'],
    'server'        => $_ENV['MYSQL_HOSTNAME'],
    'username'      => $_ENV['MYSQL_USERNAME'],
    'password'      => $_ENV['MYSQL_PASSWORD'],
    'charset'       => 'utf8',
    'collation'     => 'utf8_unicode_ci'
]);

$collector = new DataCollector($_ENV['RADIO_API_URL']);
$repository = new Repository($db);
$current_data = $collector->getData();;
$current_data['last_start_playlist'] = time();

Loop::addPeriodicTimer(1, function () use ($repository, $collector, &$current_data) {
    $data = $collector->getData();

    if (isset($data['error'])) {
        return;
    }

    try {
        $artist = $repository->findOne(Artist::draft(), ['artist' => $data['artist']]);
    } catch (\DomainException) {
        $artist = Artist::draft($data['artist']);
    }

    try {
        $track = $repository->findOne(Track::draft(), ['track' => $data['track']]);
    } catch (\DomainException) {
        $track = Track::draft($data['track']);
    }

    try {
        $playlist = $repository->findOne(Playlist::draft(), ['playlist' => $data['playlist']]);
    } catch (\DomainException) {
        $playlist = Playlist::draft($data['playlist']);
    }

    if (
        isset($current_data['artist'])
        && $current_data['artist'] !== $data['artist']
    ) {
        $artist->play_count = $artist->play_count + 1;
        $artist->last_playing = time();
    }

    if (
        isset($current_data['track'])
        && $current_data['track'] !== $data['track']
    ) {
        $track->play_count = $artist->play_count + 1;
        $track->last_playing = time();
    }

    if (
        isset($current_data['playlist'])
        && $current_data['playlist'] !== $data['playlist']
    ) {
        $playlist->play_count = $artist->play_count + 1;
        $playlist->last_playing = time();
        $current_data['last_start_playlist'] = time();
    }

    if ($data['listeners'] !== 0) {
        $artist->estimate   = $artist->estimate + ESTIMATE_VALUE_LISTENERS_NOT_CHANGE;
        $track->estimate    = $track->estimate + ESTIMATE_VALUE_LISTENERS_NOT_CHANGE;
    }

    if ($data['listeners'] > $current_data['listeners']) {
        $artist->estimate   = $artist->estimate + ESTIMATE_VALUE_LISTENERS_INCREASE;
        $track->estimate    = $track->estimate + ESTIMATE_VALUE_LISTENERS_INCREASE;
        $playlist->estimate = $playlist->estimate + ESTIMATE_VALUE_LISTENERS_INCREASE;
    }

    if ($data['listeners'] < $current_data['listeners']) {
        $artist->estimate   = $artist->estimate - ESTIMATE_VALUE_LISTENERS_DECREASE;
        $track->estimate    = $track->estimate - ESTIMATE_VALUE_LISTENERS_DECREASE;
        $playlist->estimate = $playlist->estimate - ESTIMATE_VALUE_LISTENERS_DECREASE;
    }

    // чтобы merging-задания в шедалере радио не засоряли статистику воспроизведения плейлистов
    if (
        time() - $current_data['last_start_playlist'] > PLAYING_PERIOD
        && $data['listeners'] !== 0
    ) {
        $playlist->estimate = $playlist->estimate + (ESTIMATE_VALUE_LISTENERS_NOT_CHANGE * 60 * 10);
        $current_data['last_start_playlist'] = time();
    }

    $artist_id   = $repository->save($artist);
    $track_id    = $repository->save($track);
    $playlist_id = $repository->save($playlist);
    $repository->save(Record::draft($artist_id, $track_id, $playlist_id, $data['listeners']));

    $current_data = array_merge($current_data, $data);
});

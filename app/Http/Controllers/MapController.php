<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    public function desa()
{
    $data = DB::select("
        SELECT json_build_object(
            'type', 'FeatureCollection',
            'features', json_agg(
                json_build_object(
                    'type', 'Feature',
                    'geometry', ST_AsGeoJSON(geom)::json,
                    'properties', json_build_object(
                        'desa', desa,
                        'kecamatan', kecamatan,
                        'kabkot', kabkot
                    )
                )
            )
        ) as geojson
        FROM kalimantan
    ");

    return response()->json(json_decode($data[0]->geojson));
}

public function kabupaten()
{
    $data = DB::select("
        SELECT DISTINCT kabkot 
        FROM kalimantan
        ORDER BY kabkot
    ");

    return response()->json($data);
}

public function kecamatan($kabkot)
{
    $data = DB::select("
        SELECT DISTINCT kecamatan 
        FROM kalimantan
        WHERE kabkot = ?
        ORDER BY kecamatan
    ", [$kabkot]);

    return response()->json($data);
}

public function desaByKecamatan($kecamatan)
{
    $data = DB::select("
        SELECT DISTINCT desa 
        FROM kalimantan
        WHERE kecamatan = ?
        ORDER BY desa
    ", [$kecamatan]);

    return response()->json($data);
}

public function filterKabupaten($kabkot)
{
    $data = DB::select("
        SELECT json_build_object(
            'type', 'FeatureCollection',
            'features', json_agg(
                json_build_object(
                    'type', 'Feature',
                    'geometry', ST_AsGeoJSON(geom)::json,
                    'properties', json_build_object(
                        'kabkot', kabkot,
                        'kecamatan', kecamatan,
                        'desa', desa
                    )
                )
            )
        ) as geojson
        FROM kalimantan
        WHERE kabkot = ?
    ", [$kabkot]);

    return response()->json(json_decode($data[0]->geojson));
}

public function filterKecamatan($kecamatan)
{
    $data = DB::select("
        SELECT json_build_object(
            'type', 'FeatureCollection',
            'features', json_agg(
                json_build_object(
                    'type', 'Feature',
                    'geometry', ST_AsGeoJSON(geom)::json,
                    'properties', json_build_object(
                        'kecamatan', kecamatan,
                        'kabkot', kabkot,
                        'desa', desa
                    )
                )
            )
        ) as geojson
        FROM kalimantan
        WHERE kecamatan = ?
    ", [$kecamatan]);

    return response()->json(json_decode($data[0]->geojson));
}

public function filterDesa($desa)
{
    $data = DB::select("
        SELECT json_build_object(
            'type', 'FeatureCollection',
            'features', json_agg(
                json_build_object(
                    'type', 'Feature',
                    'geometry', ST_AsGeoJSON(geom)::json,
                    'properties', json_build_object(
                        'desa', desa,
                        'kecamatan', kecamatan,
                        'kabkot', kabkot
                    )
                )
            )
        ) as geojson
        FROM kalimantan
        WHERE desa = ?
    ", [$desa]);

    return response()->json(json_decode($data[0]->geojson));
}

public function tuplah()
{
    $data = DB::select("
        SELECT 
            id,
            legenda,
            ST_AsGeoJSON(geom) as geom
        FROM tutupan_lahan
    ");

    return response()->json($data);
}

public function tuplahByDesa($desa)
{
    $data = DB::select("
        SELECT 
            g.id,
            g.legenda,
            ST_AsGeoJSON(g.geom) as geom
        FROM tutupan_lahan g
        JOIN kalimantan d
        ON ST_Within(g.geom, d.geom)
        WHERE LOWER(d.desa) = LOWER(?)
    ", [$desa]);

    return response()->json($data);
}

public function kategori()
{
    $data = DB::select("
        SELECT DISTINCT legenda 
        FROM tutupan_lahan
        ORDER BY legenda
    ");

    return response()->json($data);
}

}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateNodecontentDataToNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $results = DB::table('nodecontent as c')
            ->select([
                'c.*',
                'n.created_at',
                'n.updated_at',
                DB::raw('IFNULL(cb.id, cb2.id) as created_by'), // using this opportunity to also data cleanse since there are
                DB::raw('IFNULL(ub.id, ub2.id) as updated_by'), // broke relations here for a lot of old legacy/CDN content
            ])->join('node as n', 'n.id', 'c.nodeid')
            ->leftJoin('user as cb', 'cb.id', 'n.created_by')
            ->leftJoin('user as ub', 'ub.id', 'n.updated_by')
            ->leftJoin('user as cb2', 'cb2.id', 'c.createdbyuserid')
            ->leftJoin('user as ub2', 'ub2.id', 'c.modifiedbyuserid')
            ->cursor();

        foreach ($results as $result) {
            DB::table('node')
                ->where('id', $result->nodeid)
                ->update([
                    'url' => $result->serverfile ?: null,
                    'body' => $result->body ?: null,
                    'metadescription' => $result->metadescription ?: null,
                    'metakeywords' => $result->metakeywords ?: null,
                    'featured_image_id' => $result->featured_image_id,
                    'alt_image_id' => $result->alt_image_id,
                    'created_at' => $result->created_at ?? $result->createddatetime ?: null,
                    'updated_at' => $result->updated_at ?? $result->modifieddatetime ?: null,
                    'created_by' => $result->created_by ?? 1,
                    'updated_by' => $result->updated_by ?? 1,
                ]);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;

class UpdateClientsSupportLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $level = $this->getSupportLevel();
        if ($level) {
            sys_set('support_chat', $level);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function getSupportLevel()
    {
        $ds_account_name = sys_get('ds_account_name');
        $support_levels = [
            'adirondackcouncil' => 'high',
            'adkins' => 'standard',
            'aish' => 'high',
            'aish-il' => 'high',
            'aish-ca' => 'high',
            'arpf' => 'standard',
            'arfop' => 'standard',
            'asp' => 'standard',
            'clwr' => 'standard',
            'crossroads-arts' => 'standard',
            'elc' => 'high',
            'els' => 'standard',
            'getintouch' => 'standard',
            'wretched' => 'high',
            'hopealaska' => 'standard',
            'impactnations' => 'high',
            'us-impactnations' => 'high',
            'au-impactnations' => 'high',
            'indiapartners' => 'high',
            'artscouncilwf' => 'standard',
            'kenyachildrensfund' => 'standard',
            'lwi-aac' => 'high',
            'aac-uk' => 'high',
            'aac-ca' => 'high',
            'aac-nz' => 'high',
            'aac-de' => 'high',
            'stuartsociety' => 'standard',
            'njstatemuseumfoundation' => 'high',
            'rising-star' => 'standard',
            'saveonelife' => 'standard',
            'safinternational' => 'high',
            'elephants' => 'high',
            'livingdesert' => 'standard',
            'worldharvest' => 'standard',
            'zakat' => 'high',
            'ccli' => 'high',
            'usanakidseat' => 'high',
            'donate-crossroads' => 'high',
            'thechildrenarewaiting' => 'standard',
            'allfaithsfoodbank' => 'standard',
            'amigosdejesus' => 'standard',
            'antiochadoptions' => 'standard',
            'bct' => 'standard',
            'bpea' => 'standard',
            'shopbpea' => 'standard',
            'case' => 'standard',
            'c3toronto' => 'standard',
            'camo' => 'standard',
            'cdl' => 'standard',
            'casafoundationyeg' => 'high',
            'catalystschool' => 'standard',
            'cedearuba' => 'high',
            'celebratemercy' => 'standard',
            'decaturarts' => 'standard',
            'diosteub' => 'standard',
            'soafi' => 'high',
            'forcesunited' => 'standard',
            'phanxico' => 'standard',
            'ghostorchidco' => 'standard',
            'grandiorg' => 'standard',
            'hebronfund' => 'high',
            'itstimetexas' => 'high',
            'strongeraustin' => 'high',
            'ght' => 'high',
            'kcm' => 'standard',
            'libertecity' => 'standard',
            'loveworldusa' => 'standard',
            'ncof' => 'standard',
            'nycr' => 'standard',
            'ourrescue' => 'high',
            'public-justice' => 'standard',
            'stwci' => 'standard',
            'stscg' => 'high',
            'sjvcs' => 'standard',
            'sunflowerwellness' => 'standard',
            'warhill' => 'high',
            'warhill-campus' => 'high',
            'warhill-ca' => 'high',
            'warhill-east' => 'high',
            'warhill-west' => 'high',
            'warhill-south' => 'high',
            'tcf' => 'standard',
            'tti' => 'standard',
            'treesisters' => 'high',
            'treesisters-au' => 'high',
            'uscal' => 'standard',
            'icaleaders' => 'standard',
            'leroythompson' => 'high',
            'youthempowermentsource' => 'standard',
            'longyearmuseum' => 'standard',
            'pollyhillarboretum' => 'standard',
            'adkarts' => 'standard',
            'theadvocates' => 'standard',
            'awmc' => 'standard',
            'amaa' => 'standard',
            'bpsi' => 'standard',
            'bgcscounty' => 'standard',
            'bcm' => 'high',
            'childrenofgrace' => 'standard',
            'cibolonaturecenter' => 'high',
            'ddsc' => 'standard',
            'debracanada' => 'standard',
            'deerfoot' => 'standard',
            'demellospirituality' => 'standard',
            'dfla' => 'standard',
            'focusnorthamerica' => 'standard',
            'handinhand' => 'standard',
            'hso' => 'standard',
            'hlf' => 'standard',
            'hobokenems' => 'standard',
            'hopsicesj' => 'standard',
            'humanlifealliance' => 'high',
            'hstb' => 'high',
            'humanistcanada' => 'standard',
            'iwmf' => 'standard',
            'kellybrushfoundation' => 'standard',
            'kbm' => 'standard',
            'lghs' => 'standard',
            'life' => 'high',
            'liftthemup' => 'high',
            'ltbhs' => 'high',
            'petalpushers' => 'standard',
            'ncrf' => 'standard',
            'nmdr' => 'standard',
            'civilwarmed' => 'high',
            'canoe' => 'high',
            'pawsofalamance' => 'standard',
            'restorationgateway' => 'standard',
            'riverview' => 'standard',
            'smc' => 'high',
            'serveindia' => 'standard',
            'seapc' => 'high',
            'stemma' => 'standard',
            'bridgetraininginstitute' => 'standard',
            'tbm' => 'standard',
            'childrenscenter' => 'standard',
            'childrenscentercc' => 'standard',
            'foundation4mers' => 'standard',
            'carmelitedevelopment' => 'standard',
            'chaser' => 'high',
            'upstreamint' => 'high',
            'wff' => 'standard',
            'worldvets' => 'high',
            'braininjury' => 'high',
            'sainthilaryschool' => 'high',
        ];

        return (array_key_exists($ds_account_name, $support_levels)) ? $support_levels[$ds_account_name] : null;
    }
}

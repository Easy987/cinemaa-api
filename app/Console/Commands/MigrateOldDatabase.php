<?php

namespace App\Console\Commands;

use App\Models\Movie\LanguageType;
use App\Models\Movie\LinkType;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieComment;
use App\Models\Movie\MovieFavourite;
use App\Models\Movie\MovieLink;
use App\Models\Movie\MovieRating;
use App\Models\Movie\MovieToBeWatched;
use App\Models\Movie\MovieVideo;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MigrateOldDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cinema:migrate-old-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Migrating old database...');

        /* Stage 1 */
        $this->info('Gathering important data...');
        $imdbIDs = Movie::all()->pluck('imdb_id')->unique();

        $roleIDs = Role::all();

        /* Stage 2 */
        $this->info('Migrating sites...');
        $this->output->progressStart(100);

        $sites = DB::connection('old_mysql')->table('movie_sites')->get();
        foreach($sites as $site) {
            $existingSite = Site::where('name', mb_strtoupper($site->name))->first();

            if(!$existingSite) {
                Site::create([
                    'name' => mb_strtoupper($site->name),
                    'url' => preg_replace('/((^https?:\/\/)?(www\.)?)|(\/$)/', '', trim($site->url))
                ]);
            }

            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        /* Stage 3 */

        /* HASHES */
        $hashes = [
            "faf1afa0014c3fb566e314d7d6d59a6f",
            "d7a3135cfe7b6816e5fac3dac1221875",
            "c509415b2d44442a96faa1e79eddad46",
            "be2caf59f51b05aa642bfd7fd4c25abb",
            "bf32c776c32873f1ba374069cc285235",
            "ad522c42cab8424b136d6adc9a036c25",
            "af60890e4d92637ad5fd8e038af05d03",
            "9800742f1236c9e5f095fbb8ba52ccde",
            "8560814e090ba85b2d9c953cdbd25a57",
            "7d7b7b4e9f407b03aaaab87f1c29dd4d",
            "7172ee7ba1d5dbefad5073f1c6dfaba0",
            "6e093f8d20b1253539ce31539b6de898",
            "6bab7a933cc7cde45912c173d9930f24",
            "5fd45e650d0a9901246433998ecf625f",
            "6375ccc6b6dd110e37782351b2a8d880",
            "5d61ffb6cde663e108c1fbec74087ccf",
            "5f6cdb75af74c0e3eb589654029b7cf3",
            "54b705ee05c7bf130fa6692d63ed5f91",
            "3a687d70a12625c753c613a2abc30603",
            "2617cd7d05d3bac8dc1d35d5a6270692",
            "0a684a8aa197922b8c70f564974091dd",
            "0a9db221b8e85bbaf961e0b859cb2f63",
            "0bcb5c68d1cd0da4780a7de7cfe5f964",
            "09a2ab2d4f5543b65f7caa51e7c7d2de",
            "2484e1f6a655e39849a0d893359fc5fd",
            "a1d02fe95327a489416d3fd9740a43fb",
            "1100e947a4868191a8c7de02d2d53204",
            "b138b3560a213df8b7519550522768c8",
            "f2ad018b5b0fb13a97c6ac58206dd7f8",
            "b2e197b52517f499f6784f2b03fc73eb",
            "87e12839f6ebb1c0eff644c76c541519",
            "518e13c33e41d360f082d8749893b6b8",
            "5675824d7a2d35406bcaf1c7dff19660",
            "2064f765a0e6a8781f8c715edf1479bc",
            "8f4ec3ebeb7f863631a7b8de3682e9b4",
            "4f8aef00bdb64e51b9c406e5ca8db0e2",
            "d70b8f0cd66e48e15a0b354d7226f36a",
            "a28d208a9eac3c4301ceeacd60480a85",
            "d48a6ad498a4b0cb9f7ba77eb925b9bd",
            "24ffbd7cca0ebb0cd921f8ec63280807",
            "da2cd423a8fee8b677ad1f72bf2f5f17",
            "5b35a4bacd1319b91b2e92680b7d21ec",
            "5cb3fe19d8e6966d25bad582994a8c53",
            "71ed67094c9a00e41c832459dab0c5b9",
            "b27bee68fd0026c97cd454f244a1372d",
            "cf2a89573669efa1d4a8b7b74c0a8b00",
            "e2635501a37730f8bf4143dcae4dca3e",
            "2f3da458d406fa0ca558f8fe9ba7caee",
            "f5f4e5bd99b6b69819ca5b7991fc5405",
            "9eecaa94fa2f8e41ab69111e67d9458d",
            "35a62609374565d4c4bc00e8ad0575ec",
            "700494179c20621b83885f00ace41079",
            "9e6481d0b8523a7cc34d69b57c9f0630",
            "db3f5f611883f0cbcc282875b01a7f61",
            "20ff25949f1699ea0f59e72691571218",
            "359eb295ace99ab8ee6dd538dc08a19c",
            "d1a023ff03157f0ae50a8bf592cd2070",
            "62a0b733906203847673ce3854afc11c",
            "8c373d5ac62667637afbffc29831573d",
            "8a24dbeabd313023bbf4e37b327ed34a",
            "ce4ac022637f61cfcaa34de98aef63ba",
            "e4bae752a9aaa667bbf064253512a338",
            "e499c2f4dc4103e8e95ecde9ef8f2d6b",
            "541af0d1f512714890024fb6b7d84898",
            "e051bbed2bb64f7adcb9fe8dac6d4b19",
            "eb27022143c3baacbc22a0cc604ff7d9",
            "532abbcfb087b40e845d1ecbb57bf05a",
            "610e2ae36ebe441fe3952f5e6dc0df17",
            "dc3d2a0affd58a079755ffafcc293001",
            "875477c566251d8a4d7f19ec2b1f01f5",
            "dec039349a44970b02333636bda51d65",
            "8b19bd4b4b449f97fc869da0a409dcf6",
            "eb0d4df846e66043489a45d34b6383dc",
            "481a80c9c4ed36379095a67c1ea5289b",
            "05935b4090e3d57dfcd97a329b16d020",
            "f87dd58e682afda58f684b8672f2edf8",
            "4dd5800aff0a4f92c81e0ec803e0e06b",
            "5c9a504dd7875e562bf5ddcb0286e2c5",
            "7e72d6819904458dcefbb4bb3a910265",
            "861defb510760a99c84a79979b4f6b44",
            "3972f470a5d32d1ae99f419b9d05010b",
            "40d371dd2048ad03243d213f29dc6804",
            "c7a0dfae1f30355efdc1ea16be93ad13",
            "eb3f2d82471d46ed8e4c3dc0ad3cd356",
            "85e73bb0df5f97e611e5df40484cb870",
            "678c440d371a1340e7801ececc865777",
            "257189b8e3d02fc467a153f191fa6d0e",
            "0ff361b0950e0377b9d0bd06e215c715",
            "941e8b3753bb93a26502722c73337b4f",
            "56c1c3bbb2f6b9184c87e4c109d0e80a",
            "f0fcabbef3a2ddd7a0fb6b9ec27923e4",
            "d7da408a2278e147ca18bab3ec9bd241",
            "99fe72db301569854afe92b90f4a4993",
            "4163b896f629c2add47b921a9718afb6",
            "727e89d78e4136e502ca06240c4e6c13",
            "10f1e5042d4f76fa07d640c6f6801eee",
            "e1c034be4b237ed43407b7236b71b2d5",
            "78c87ccebbab311a091d3ebe3a2ca51a",
            "8eaef781803432c2f915a96c5e7e5c47",
            "b759f7da49bea47cb083557a616823f2",
            "faf22842062a67bd63c9cd121c8a69b2",
            "d33825fb4938510b33b2e165302b85f9",
            "19eb9ad55ad951dedfa043a85d991e87",
            "ddf0ac3e530ade9aed868904c8b4d5a5",
            "f29e144fe317a2d128d1038f090fdb89",
            "fc80a4380fd5823f5d8634fb4c46f828",
            "e6b1f2fbe88547739cab99e0f13f5318",
            "2c22b1023073b5b0b8988041c4ab4cb4",
            "0ef9baf7813d41300ee2b4df09243ead",
            "a8314276cb8089cdda4c461bbe89b18e",
            "078eefea6dd16950def670032b73aa90",
            "969661b2cb43f0e7822838b858b44283",
            "28aae9a344fbba849940abf4e1c786ee",
            "64e9db9bd90607d2565165224bcf7d12",
            "3a71db83977b6cb1c5b6eb0b672b9dbf",
            "059bb02ab2d7654ce306477d48a3efb4",
            "931c5db1be2df8c1494e50097d913a2b",
            "75c4ef4ce370cdcdcb5619aa1fe39602",
            "bbbfb158bfe5cd49fddb978b65da4fd3",
            "0580539f8cbb4771d39b447657a35d34",
            "fdc1c0ad6d25995d790862c5229b6b57",
            "e7e9f09acaf33c785fe178a1df58b4bf",
            "2f5c50bb36619d700e7db44580e52fcb",
            "d89f83d2578ceebf3b2bb98bf3b09cdc",
            "9f5bbbb913e86374f3c6f7a61c2ba29f",
            "e900f5d04108d5fc218befdb3a5ade50",
            "99f459bba44b51433440abe94ccd29bf",
            "ec6f99e6d0ecb8f84a79bb2ee41d250d",
            "f33bcbab065a5eac0704ea4b44cd32ce",
            "e0aba8d51134e07bae9099841cb90192",
            "ef13d86448ca70171f35f144f0a4b75c",
            "7339ec8b0eb434d99bc60a13a0d95154",
            "1e3e3d8e93a22e43f293b487c0f19bac",
            "1e760d59fa81a6fc9424a9dd48b17cb3",
            "ed484efd86c9d083c856cad1c9ca2bb2",
            "85008623ef3586080642f2dd629d5ed6",
            "30816c653ba6f041df8c449f13a03426",
            "7461f52e488aaca2cdbd2ad63812daf0",
            "081f677ef6b226c17d463d27d19c7184",
            "1dda4ec30ea7275a557bdfb3ffb26e9d",
            "54a299a0296b2afbecf952d08c664aee",
            "c38337c016b34ccd6808ba07b2e22d5b",
            "e3f98e49b050809513ff60f99ea5250e",
            "8c21682727472379d1e57355bd68af82",
            "0d2b04ae752e7c92f87b9057e10cff91",
            "fa636052af336c3459ac316ce89029e6",
            "995d10aa40ad7646ddca0e95140cea3e",
            "d5a2614892a4957980f2718ec11db169",
            "6bf1b7ca1846f67acd6008f3db56d68a",
            "159fec7f33e97f62770202ccdc3947e8",
            "be2278dba406b6a380c62cfb1323ab4b",
            "10f19eb866031d784e74e12f5a289d2b",
            "e23cc2d5b768caa17b5e40df54bc5465",
            "5ef5154bb7d0cc5b9a55ec3ee7c7cb4c",
            "96790885d0491254e5c911d4d844c6d7",
            "cfeaf3f06bceb9c99455441b454b2148",
            "921ab0adbfc4bfcd5649bd1a3e4d9347",
            "01556ed17d0f393597d2208df1e111ce",
            "06287f56121b1d1b55135be482a987e8",
            "0639fd5c186a913001b6e7704c1b90d2",
            "080f896536668c2a52168b27c1786c6a",
            "0ac117ced8304539211bb424d632c7d4",
            "0bcad02cb0d7e7460e42f8fc82da3ebe",
            "0ca452118ebbc84a93ab3b6b44841a34",
            "0dbabc9843bad1f10466b1d6821ea50a",
            "10c94fcf52a177190b93970510d3eaab",
            "134d446bd73e960952e4890ee3d91381",
            "14283d76c1e9be2a5e754346efa59741",
            "18453c8028725e06a08590671de0e70c",
            "1a8b8dca3fcf7afa11fe6fb54c5fc562",
            "1c064436a2395a494c00ae6a0edc0c30",
            "1edb3ef498148d3f819347a0c99f9226",
            "2254811b753ec50c1866b53db9133bfd",
            "277b843d10a3114a3401fbbf88ce0888",
            "2a3b57807a4f833c04a598caaf424b36",
            "2c7c3743979fb5fd5c2b0b6777e8b8c4",
            "2d3dcbc7f3cfa5242ee8c41e5005606c",
            "315385eb349b174a1444fcb14f16b7a0",
            "31c5b7b3f93c9bfb5246ef5430d19daf",
            "37779c9a5dbdb6f8239b6914e88aebda",
            "38a16a7a4a1da7374756dbcff9a9c1f8",
            "3a7e4d2480d4b74a290aaf55d1dc7c81",
            "3dfad7a0d8b69c18246566efa0f92425",
            "3f0cdcf45428ae0444b8f5a910648b85",
            "4114cf2f0551e021004702c79c93bb7f",
            "4207c98753f25c0a1d8f7cad8cdb103e",
            "457828ba0f0c72188086075872454ba7",
            "45c603d405805548e3cdd9d5a386b4e2",
            "4624f565844fd8192496b674fbfc4825",
            "46e253135aedb129767dbf57b6b4a859",
            "48174ba61b0ca031276a13fb2096e22f",
            "49284dfd90e3e30f47b7c362a0e7aa1b",
            "4dcf0c4821a697f2c5306fa572db6c0f",
            "4e719d752eadd72c9cf8039562aa44bd",
            "5018b336f5ed2312232e5802fa04b0d0",
            "507446b15cf01d1c902a66400cc8e60d",
            "50915f27095a25facef6973ab2708473",
            "50b0a963cfbd4605e6f39464848efbb4",
            "50e563ab527bcd297ec336dd5c69d711",
            "53a147473a47d01d2baebd9726a794e4",
            "54d7202c05198f3ef55f676b051a8707",
            "5521cbfb0dd56449caf5b49de6a529d8",
            "55814b6815b5c7463971233343af8ca2",
            "5806a3b436882522957a57852627ba1e",
            "59c1dc085d6070252491bfad07b34be5",
            "5a4f147ac47fd12ca2623d6d637b336b",
            "5b9afc8e5c52eb37e65bb32ad9e82ec9",
            "5db732897122f703e6ce68a79669077e",
            "5fda675b11730f2f56218be6950e88ab",
            "60ca3345f2e332187e729c3ff024c1cb",
            "60ea146238eed01aaa140ee085f5f932",
            "679f1efb8139a8129cb662f7081a27ba",
            "67a1dca2673e140835ff82c3594dbfa7",
            "69b9bec9df53ef043efd53e4b6185066",
            "6faead8a37ba9848939497c854d107a3",
            "70031e75062a572c3fb417fba509f60c",
            "7332bf2c0c91ce1ab933bcbd7c4d458a",
            "763e274c892d4910342d432ba9280217",
            "76d5735944a15d3112f5688ede4cb5ec",
            "7d02df72a49a351ae99e9f41675fd1c9",
            "8182a4d8641bb2322562b9d441eaf75a",
            "82b302114a3a0542bef8cb536c5d83b2",
            "8349b98660eb1e1f00135c7bbb95dc42",
            "84b55d6023632e09ea4d883b1a7b69b7",
            "85d5a9846fddb11c6265eb5364f53d3a",
            "8988dc718fb10918632e5d0eddfcfcff",
            "8a5b9d0850bdddfebe36a1ca210aa59b",
            "8aaea788913409315d84b53c66eb2227",
            "8d649dbfb8bbee2e98a7a2f3031139c5",
            "8e3509ac51206038945be1dbf205b807",
            "8e479d520684550cd68f1ce0c8ed6957",
            "8ea087001d847c3fa2ec96972bc0a66a",
            "939ef6c4db70f22932f11e971669af95",
            "93c9e5d32af6b608a8decda9044fae2e",
            "95e22f5ae5cf853b9fa7f893d6858b71",
            "9aa6d07888318709eb886ae44ac09abc",
            "9b75f67776de0d05538becaad8f14d75",
            "9fddf2eb4a8a110a7a5af85959183664",
            "a139823c118c91f5691211ac8b82637b",
            "a456b48c1859e6432f844bc38e310052",
            "a47c9ebdaba2f0fad161e42369987059",
            "a5925ab4156eda3b3d81026f9e014e8e",
            "a62a747b2fa9183e0670cea08f659e8b",
            "a79f7a0bf18a5549bf6e36c513726ecb",
            "a9a8fde94ffb5010f7703b060a4a4740",
            "ac3dbd3dc0b8f3312d617e39492e4d29",
            "ae6a823fc1de2fffee91b5718685d73f",
            "ae9a7e93b7175402155cce0f841a88d8",
            "aef0761157907214521d27d76b811e0e",
            "b1be359772ecb613b457509ab357df52",
            "b27ec5cc64eb881ce599885cde0d3990",
            "b2a40a0312c50af9b8843f0e7d839ead",
            "b37b60c92fcb0608a86620f903b51038",
            "b8dc118208536b05a164467360beb1d0",
            "bbb00ab4d16063331f5fd3e1a809ed6f",
            "be34138c9edefff4221a54034f913704",
            "c1f65fcddf81f444e1541b06ae59636a",
            "c238d25e64e9c5436228ba743ed38285",
            "c265099a184f972c9c07d311c779fe7a",
            "c415f7e41333364560f681aa776e6f97",
            "c6a27ce8bad4d006ee0af37c002bad51",
            "caf566b3f29b2306048d4e4c1fc71921",
            "cc42cdf70e6c170168ebedbb772471b0",
            "ceea90bf25f18311717bb8af5e132479",
            "d073585d8cbf500410f635d1d5be675a",
            "d4ea0806aec0cf3f3d8a6c1e5327c2c5",
            "dfbac9c93ea89e0ba3d0312710816200",
            "e186b3133a362c1000ff957ec038280c",
            "e51f1c1fe4447406e579906c6f074eee",
            "e5a06264828bad1518284785dcafc6c8",
            "eb3c6ee08fc27524c59899a52e9037e4",
            "ed5abea4b5b4fa1df9109fd87fb3e7fb",
            "eebc89a23cc34c29621780966c31a035",
            "f01036ee45fc8917a296c80e0fa419c9",
            "f043db7ba1c366066f512c87fde4c23f",
            "f2af9e71e08b4a129f6689d2e467d86e",
            "f78b0761ed0c35f219aa47808a66c8fb",
            "f827c32f5d3874f67d6348b903b25c63",
            "f9b53d61beaa9102f8a1e0afe96f7b01",
            "fa3be3239bda6639ab055c683db7059d",
            "fae27797d4b6391f238b424a2046545b",
            "ff63d7238b73e63643d6c65b87637064",
            "02b4159ab62a815c6a178a08c81c8649",
            "02b81b53f2dc6a4c1e72d3727b309cb1",
            "04b363e8c02c80555ca18c2308287693",
            "063a6e0d95f0ec4db45ea19a46a5077d",
            "06642075c8bcfc81d7d5dfc8116fd886",
            "06927d3b236e429792a9dc1df04be72c",
            "06da7119b3f79380fb5df1a3b4e3d1b2",
            "080004ee780122a42bd391b96eed4e32",
            "086c0037399d12a7413e0fbdfa0fd554",
            "0a1cac066ed35ba1bf793417dc1af305",
            "0cd3ab5cb64c2f79a01c17f2f4482422",
            "101a5c17b02e6d95a7e63537f427aa0a",
            "113593a03216c76ab5a92e62d9eea023",
            "160a21501ead315be68f8620702d237b",
            "16f7ce318664058cc6fa6fd402f9a315",
            "17061382504b2da4fff611a55cf6b059",
            "178bfa8cceeefe9954400f609b9d492d",
            "191510692332ea5110fcb3fe0bb93555",
            "1b64a113d745a08380930e491687de44",
            "1cfaf48d5370df5fedccc18eafbb4670",
            "1e8185329be5a2a357860be84b964453",
            "251f671d7c2b06669d61dfe5eafd22c3",
            "26ddedff73d8ba8c52e5e0a082d4064c",
            "2a0ddccdb91e3d4bea70581856e7f2fe",
            "2a2bce28c046a5a41c157e04fe14f75c",
            "2aa2ef45246dfb88fffe910b15714baa",
            "2ad1f6f97f4c5e7ef11dcf003e860cd3",
            "2b28b7aed4a91952b7ae1603e1f205a1",
            "2cc566325ae0ed65e5268dbd2a4a0a4c",
            "2cd416bbc7a8a45b1c32edd162f78cd4",
            "31d15ce55fa5fb57a59d1ab476e5692e",
            "32a6f77ccb68cc54e3c64385e06f3ab2",
            "33982d5c13a41b7115947a4780dc8125",
            "3ab06b636dab6679c4fff428af25acf3",
            "3ca50ad3d9a9606855b52cb13be58bac",
            "3f52e822905401954d7f7f998e3a444a",
            "3fa397ccc48b95a21d8b0eb792932910",
            "4319174679a93f7569909fc414fa0afa",
            "458197b07faa0e89fc464ed5773b0804",
            "4a2d408217546777e3442cc2ea156524",
            "4b27503810ccda5b12d8e0acbfffa0b7",
            "4d8602dae7e6796fb1826649be72e017",
            "50d0c4c71e024b5300172ae454e8ba14",
            "51d2743902490f909c6ace8315b08f25",
            "5a6bae827268636df5a1fe2e22e8f62f",
            "5c837df0abe8186f00dcc08b28a2df1f",
            "5cf9f27dd53d6191c10afd0c7f73ba6d",
            "5df5d79127c2a098c53a76c2e2b0a746",
            "61bb067398f6046ecfcf9ffa884efee5",
            "6242f93c7509ae5d5abbcc355a395b5d",
            "64757f0f37185c5595803c08016826f2",
            "67cdf3e984281f693dfe98ab77f12519",
            "6a17058c7a340e8e6761cad3f91f3db8",
            "6ed53f4c8f3b09800789e76dc4c5c74b",
            "6f02127ea302fc78739b1d4e838cd740",
            "761d4aa583b5ee6a9ccbb0b4cf19086a",
            "780e7de42854e095da906d7193925ef1",
            "7ddc4179686def79e570b812b48c9e05",
            "82a617a801a01d8b11c809ca7eaff9e1",
            "82a7a52a2da5c27e9e4ad8dc80612482",
            "84ae4863b3c624570ddfdf2175dd0d99",
            "89086f77f3735077a9d276aed3fac164",
            "89be6be000ba4c74ff1dca24252894e7",
            "89d85e798e132d5e166f8be8f49ecd9a",
            "8a5596997776ab720797537af6e5c754",
            "8c1c8c9122090fa80f79aaf16dbe2b58",
            "8d6a9e738e3a67586dc372d5d6cf0d09",
            "8e04cf58e1dca28d149dace4b1388348",
            "8f370cdfcc6e5476c81633a2d8264d05",
            "9189844d540ece55182ed05fdb31ea65",
            "9258dab827f09c82ab5f86e6ebdcb871",
            "941afa78b1e425a09a9a915a862aa41f",
            "9a8f5af48bd7257537243733510c7765",
            "9ef52625e5d254641808598001ba8727",
            "a0af0ea487f75b6c6a1279a13e515ff4",
            "a0f923a567691b5fc3608fbd7e8be455",
            "a16b420bf30bb45e2fae1a86e8fdd812",
            "a1c110b1fe23f06ce8952cc820a2897a",
            "a2add1fed30bdf5319dc0cb822cf3c71",
            "a3f93c3fb5a3b563a38c56ccc9a9cd8b",
            "a5e67ddbc73611a36bef6141ae824346",
            "a83a147df2070d5f908823fbc9224f5e",
            "ac110123fd11d3998b5a464df227b3e9",
            "adca43e32de5f4800c863a679ea51af8",
            "b089dad1ace83b838a8afffb07389477",
            "b28b5374b14f9a929101580f0e72fa1d",
            "b2d265ae5d394599f448e3adaacc6969",
            "b72a95da7123369cc6c47564c34b9746",
            "bbf4c955be244d6d8383a07d39537355",
            "bca0b60c6b338a7de2798816b3cc9cf5",
            "bdf1c117911ffb70c45f578c8ab811ec",
            "bf401856689bb64d3554bff773e30932",
            "bff1504d116ef0533bd9b17ca8ebbc6e",
            "c607ae656517cc4e4a48a86eade73f66",
            "c611732092eb609513dc10093626c517",
            "c6bfbd075f67618cb0a4c99a0bb5a143",
            "c9605866a0e7255540526eb47597320b",
            "c9833694177ac39416aae626282a8cad",
            "d032f4f8f493d36a8c217ad5388521d2",
            "d0b14c0814b81972998c196035b3b799",
            "d399c954b32b6f5fd72cde7786cf323f",
            "d44b3ea3b812c25cc200fae61eaefb65",
            "d871f76541050399c22dc279fbadcd6e",
            "db32430a717d988df154c65342492562",
            "dbe48b2671319b57575b80bd4059091a",
            "dccedcb60c261075adfe4f5720fdf5e3",
            "dce7d6441a21ce71495ecf9be29fcf08",
            "dd5d8ff51bf2ea69d0adc632bc5f2906",
            "ddb56ddb5e02206b5b4a67a7e1896324",
            "e0657c06ed6cd1654b973a97fb704386",
            "e250e5d250f6cda6a69a1bf5de5bbe31",
            "e412b4c5492c01915f6169b034683e45",
            "e67cb3b7fcc199883d08fa76c5b95a84",
            "e78fca6b99853c59a7aaf4bed43a56fe",
            "ebe847d9e10385854d0a817c7ad66ef1",
            "ee4ded1dfd925c9171f5d21aa68d5240",
            "f2ec11d13b8a70621ccb9be99dab696b",
            "f3c2f62d3bafd34d9ce8b09538fb9378",
            "f6c325bde828116591df68c8f45689cc",
            "f85d03eaffa4828b69f1c7005d084b63",
            "f9ea725d8a09c591fb9c23f1073861d5",
            "faa6f52132eb5b4fc1673b130204058d",
            "fd329c2c4c25cf56cd34db1f7f63b7e7",
            "fe05c0408d47049b09a1f072fb214fc6",
            "fe99c7b3749c00833622afb4b8b3455c",
            "8aa48398c82b572b061084a88cdef443",
            "dd61dda32a18731f8fbb193303f43b2a",
            "34d1761cfde712653e63f30b5441b142",
            "c58878b37f7716b1f59def07e7690bf9",
            "d7c3f4f3bba1311200a2fc853d689c30",
            "d741b808c5f8529fcb445e5c87eaa66c",
            "25d82c900bc6acbf586a6a1ea0cbce9c",
            "bbf16e2149c8a6547c8c9cd6eecfd53f",
            "f215740a3ad29ceb9e852a8af9d3f977",
            "a55552f7be3f79fe884674ad3c5e46dc",
            "7efbbaa2d17edd7d2d830be42b9e5f08",
            "b86861782edabececba1bef5058d0293",
            "e962e073e6b67108cbe8ce48168cc59e",
            "46b6f19fef3092d76f3a6ec3d678d4a3",
            "855bbf1c142dbab4e37049c550b68f2f",
            "cfbedbaf78b890f288bbc7589b86e105",
            "e1831cbfe13139ba3415b35e31d3b3d5",
            "3ed198b50f744c9e6b61be2104486945",
            "a24a9ff20eafa79dae0366919ebd2b63",
            "817a9a8f502947a91de56a51f75d728c",
            "230990e49ebedc00067c849e283d3748",
            "4a20f595e56dfec6fdcae02b024962cd",
            "c6f807f7ac290f91f5ff6a1a6753dfd5",
            "149134175e8b18fb408b027d6164b347",
            "f39ec60a2d0123453595b524a742ed79",
            "9c9b736f6214164d1347ebcd7800778f",
            "4ae4f1f63af2edb7d5b10538813c1220",
            "7a26d2672581309ba7ed383a6069b92a",
            "7f1a5cf969b46817992ede735e6a2c9e",
            "85698439ea522c0e620a08f9e6d306f0",
            "9a029488bbb46d784962236b3c825c29",
            "c8b3b1d11cf7d021ec728b73d5b20028",
            "c62f82ae5a1af1c7da876f832551613a",
            "e3c2a643940b1cd0442831f45dcee08d",
            "49774845960d652d9cd77fbfd43d4822",
            "e15c8a41108b0f27be86c5ca1d4da723",
            "69379a14318f8b9c285e336da8f0c665",
            "c4d48bfbfd6264ab1ef0f3d599f04cf0",
            "17957559d752a4f7e01111706e3051ac",
            "8bbc253808d578f7ef127575cc61fb6d",
            "bd4af5a720f63b7d26db07c9257435f5",
            "decae7b9b82a5759aaa90af95ca4d695",
            "427ddc1819016435d1467c74c86aeeef",
            "b8f4332eaefada7a3e498470dcd2d421",
            "739e0ff59e0f16c75da4b672112d5494",
            "06611da2ed11768e39a74fb178e80b2b",
            "75830b10cbd28ca1eaa3d0be01cda847"
        ];
        $emails = [
            "piszi1975@gmail.com",
            "slotgameteam@gmail.com",
            "magdiderivo@gmail.com",
            "djkukszi@gmail.com",
            "lakszabi@gmail.com",
            "petya1112@gmail.com",
            "highdefinition@citromail.hu",
            "malovicsdori@gmail.com",
            "vindorfsedona@gmail.com",
            "lamia.canis@gmail.com",
            "jencuska@gmail.com",
            "furnsteinemese@gmail.com",
            "nagyzsofi43@gmail.com",
            "enter070707@gmail.com",
            "ronaldpeter851@yahoo.com",
            "5305icus@gmail.com",
            "madarasijani@gmail.com",
            "reka.guszti@gmail.com",
            "tiborszilagyi1982@gmail.com",
            "baba@gmail.com",
            "pkollarp@gmail.com",
            "jakab0913@gmail.com",
            "mlaco1970@t-online.hu",
            "anita100@gmail.hu",
            "beni73@freemail.hu",
            "bedoan@freemail.hu",
            "boldizsar@gmail.com",
            "buzasia@freemail.hu",
            "cmwz@freemail.hu",
            "frankogreta@gmail.com",
            "gazda6@freemail.hu",
            "gaba1234@freemail.hu",
            "kisberi@citromail.hu",
            "kisslas@citromail.hu",
            "kocsismari@gmail.com",
            "martimez@gmail.com",
            "mirog@freemail.hu",
            "nabe@freemail.hu",
            "noncsa@citromail.hu",
            "nuner@citromail.hu",
            "plorant@citromail.hu",
            "ros90@freemail.hu",
            "sunline1@gmail.hu",
            "stel@freemail.hu",
            "timargabor@gmail.com",
            "timea85@freemail.hu",
            "uindiana@gmail.com",
            "allex9@freemail.hu",
            "angi42@gmail.com",
            "biuscica@gmail.com",
            "gorbacsov3@gmail.com",
            "gunnerwin@citromail.hu",
            "irotasjoco@gmail.com",
            "irotasjoco@freemail.hu",
            "j.l@citromail.hu",
            "jnorby1@gmail.com",
            "kaszas45@freemail.hu",
            "kata0907@freemail.hu",
            "magdalaura17@gmail.com",
            "mogyi44@gmail.com",
            "morcika1@gmail.com",
            "mufurc1989@gmail.com",
            "picasso250@freemail.hu",
            "repoman730@gmail.com",
            "rolcsi2003@gmail.com",
            "sidius66@gmail.com",
            "suzydenes@gmail.com",
            "talianviki13@gmail.com",
            "tumpuka@gmail.com",
            "skuczi@freemail.hu",
            "tusy89@gmail.com",
            "atyimby@gmail.com",
            "ljudka7@gmail.com",
            "blog992@freemail.hu",
            "vnesylvi@freemail.hu",
            "kojiki64@gmail.com",
            "csenge71@gmail.com",
            "seatalto@gmail.com",
            "newsedona@gmail.com",
            "pancsilla@gmail.com",
            "lszloboda@gmail.com",
            "nomika2449@gmail.com",
            "kira200270@gmail.com",
            "k.david0713@gmail.com",
            "kissbobe0807@freemail.hu",
            "donamonica02@gmail.com",
            "nagyhoho5511@freemail.hu",
            "halszmisimihly@gmail.com",
            "vegtelenmt2info@gmail.com",
            "alexandrapatkos@gmail.com",
            "szabjo@gmail.com",
            "ukta59@freemail.hu",
            "agipus@gmail.com",
            "fogasp@gmail.com",
            "tgj4802@gmail.com",
            "hdomi05@gmail.com",
            "majci001@gmail.com",
            "ihaszdia@freemail.hu",
            "atfagyva@gmail.com",
            "laci0660@gmail.com",
            "egyedemi@gmail.com",
            "bubuatti@gmail.com",
            "sameszka@freemail.hu",
            "borcsi05@gmail.com",
            "totyajani@freemail.hu",
            "ildiko427@gmail.com",
            "gizus1965@citromail.hu",
            "dudisebes@gmail.com",
            "jocika321@citromail.hu",
            "vanda1209@freemail.hu",
            "fiona5090@gmail.com",
            "bence8172@gmail.com",
            "nagy.rasi@gmail.com",
            "pittano44@gmail.com",
            "jimiwhyte@gmail.com",
            "takipister@gmail.com",
            "apollo2866@gmail.com",
            "vjozsika13@citromail.hu",
            "spartac2os@gmail.com",
            "merien0326@gmail.com",
            "bokorrobi76@gmail.com",
            "sanyi.hajdu@gmail.com",
            "morozistvan@gmail.com",
            "halak.motor@gmail.com",
            "max.romulus@gmail.com",
            "mazsola1003@freemail.hu",
            "discobear74@gmail.com",
            "nyaradibetti@gmail.com",
            "kiralyedit57@gmail.com",
            "reszeghilona@yahoo.com",
            "vereserzsi69@freemail.hu",
            "appel.attila@gmail.com",
            "szabodora0227@gmail.com",
            "andreaplantak@gmail.com",
            "koszegivagyok@gmail.com",
            "bardos.roland@freemail.hu",
            "gyapjas.zoltan@gmail.com",
            "balintsolyom88@gmail.com",
            "valentin.pin98@gmail.com",
            "sefferzoltan98@gmail.com",
            "csincsitunde72@gmail.com",
            "anderanna.zsuzsi@gmail.com",
            "annamaria.szelei@gmail.com",
            "kisgyorgyilona771@gmail.com",
            "sztankovics.istvan@gmail.com",
            "ahimes@seznam.cz",
            "fater@mail.com",
            "nanoka@live.com",
            "kistas1@msn.com",
            "naspi88@hotmail.com",
            "csegege@yahoo.de",
            "dan-derx@hotmail.com",
            "valomarco@hotmail.com",
            "bmarta411@hotmail.com",
            "kentaur90@hotmail.com",
            "monika9718@centrum.sk",
            "mihalybalint@hotmail.com",
            "pintergabi07@gmail.com",
            "ferenc.solyom85@gmail.com",
            "szabo.roza28@gmail.com",
            "szaniszlodorottya@gmail.com",
            "kissanna.47@freemail.hu",
            "blasko.andras@ajkanet.hu",
            "daniho140@gmail.com",
            "gaborfulop12@gmail.com",
            "kekesivanda29@gmail.com",
            "edit.meszaros.uk@gmail.com",
            "gretimaci9@gmail.com",
            "kissaaqw@gmail.com",
            "nagy.agnes329@upcmail.hu",
            "hovatun@citromail.hu",
            "lengyel.bogi7@gmail.com",
            "kisscsenguci@gmail.com",
            "sikeres258@gmail.com",
            "alizzeus@gmail.com",
            "pappbianka272@gmail.com",
            "rajtiklaruska@gmail.com",
            "violaaa.266@gmail.com",
            "mz_iosif@yahoo.com",
            "borsos.tamas@hotmail.hu",
            "tothsara32@gmail.com",
            "palai.pucieger@gmail.com",
            "sz.dori12@freemail.hu",
            "sutorizsofilg@gmail.com",
            "kisala@vipmail.hu",
            "szedermartin20@gmail.com",
            "rea@digikabel.hu",
            "atyacska1973@gmail.com",
            "bartosgyorgy7@gmail.com",
            "repasi.gyula@t-online.hu",
            "tundeszondi@gmail.com",
            "morosz63@gmail.com",
            "ancsucsu49@gmail.com",
            "naadika12@gmail.com",
            "ritkarita2001@gmail.com",
            "hazelmightylock@gmail.com",
            "bkrgbr2@gmail.com",
            "patakilili2@gmail.com",
            "attilaboros1986@gmail.com",
            "mihalyeszter0612@gmail.com",
            "kissvanessza9@gmail.com",
            "kenneldarkmoon@gmail.com",
            "talexa0303@gmail.com",
            "mazsu1@fibermail.hu",
            "klemmadel13@gmail.com",
            "zsigalilla2044@gmail.com",
            "beluska52@freemail.hu",
            "forro.martin06@gmail.com",
            "aletta.licker09@gmail.com",
            "leventeh2005@gmail.com",
            "hencigyarmati@gmail.com",
            "toopoolino06@gmail.com",
            "varaljaisara@gmail.com",
            "arany.veronika@gmail.com",
            "csengereka2005@gmail.com",
            "szerencsecsillag71@gmail.com",
            "baraturyistvan@t-online.hu",
            "sacio1945@gmail.com",
            "kormanyoshajni@gmail.com",
            "ernoco76@citromail.hu",
            "fatboy69001@gmail.com",
            "zsofiszonja2005@gmail.com",
            "malyeramina0610@gmail.com",
            "kasziracsi@gmail.com",
            "fekekingaa@gmail.com",
            "hpr1223@gmail.com",
            "19571218i@gmail.com",
            "lorinczviktoria402@gmail.com",
            "papalex.sandor@gmail.com",
            "lbitter198@gmail.com",
            "vivienfuz7@gmail.com",
            "kapusibogi9@gmail.com",
            "bielikbarbara@gmail.com",
            "wepipati@freemail.hu",
            "sznb11111@gmail.com",
            "aradilaszlo@index.hu",
            "hakanturk3@gmail.com",
            "bartovics.vanda@gmail.com",
            "mitrovantimi@gmail.com",
            "inczer.e@freemail.hu",
            "gyorgy.lora@gmail.com",
            "szzsanett2004@gmail.com",
            "brasso.evelin@gmail.com",
            "hunor0126f.l@gmail.com",
            "pappnevereskrisztina@gmail.com",
            "szjozsef9@yahoo.com",
            "oldalne@invitel.hu",
            "farkascsaba7777@gmail.com",
            "evareus2002@yahoo.com",
            "gergely.janosne@freemail.hu",
            "csokislany82@gmail.com",
            "biankasimon240101@citromail.hu",
            "szentkereszti.eniko@gmail.com",
            "kulcsaradri@gmail.com",
            "kosnemagdi@gmail.com",
            "vargadomi2007@gmail.com",
            "greta.gyaja@gmail.com",
            "kistalas@icloud.com",
            "tibor28@indamail.hu",
            "kovacsbia2@gmail.com",
            "labancz@biztositas.ma",
            "vparej97@gmail.com",
            "szamosan.janos@yahoo.com",
            "hajni9223@gmail.com",
            "mercike2001.petro@gmail.com",
            "fokilina13@gmail.com",
            "viharmotyo@gmail.com",
            "alcatel910703@gmail.com",
            "vindorf1@gmail.com",
            "alexamolnar25@gmail.com",
            "balisblanka@gmail.com",
            "galavitsnelli20@gmail.com",
            "kornelia79zoeld@gmail.com",
            "schzsofi04@gmail.com",
            "dora.kovacs88@gmail.com",
            "ebogika@gmail.com",
            "pankasprivate@gmail.com",
            "raczniki88@gmail.com",
            "sweetangel740602@gmail.com",
            "nojika96@gmail.com",
            "jegvirag29@gmail.com",
            "emese19990320@gmail.com",
            "szosziboszi87@gmail.com",
            "pappzsuzsa991@gmail.com",
            "pannamoln@gmail.com",
            "dodo.szeri@gmail.com",
            "brganunc2000@gmail.com",
            "erizella.anyuci@gmail.com",
            "nemethmisu@gmail.com",
            "kirabubukata@gmail.com",
            "burailaci46@gmail.com",
            "leitnan@gmail.com",
            "goczeimrene@gmail.com",
            "szabados.ballonos@gmail.com",
            "hardi.laura.1@gmail.com",
            "reginatokar1710@gmail.com",
            "kostyopanni0822@gmail.com",
            "komanikolett88@gmail.com",
            "evelin.orban1995@gmail.com",
            "dzsenii0302@gmail.com",
            "hamar.fruzsi@gmail.com",
            "cintia0621@gmail.com",
            "makoszoltan44@gmail.com",
            "gergyevanessza0125@gmail.com",
            "martinakiralyszeki8@gmail.com",
            "timimireisz05@gmail.com",
            "alexamolnar256@gmail.com",
            "sallaianna01@gmail.com",
            "seelererika@gmail.com",
            "piroskais@citromail.hu",
            "vedresgabi5@gmail.com",
            "magaygabor@index.hu",
            "jozsefpapp1961@gmail.com",
            "attila.valu@gmail.com",
            "djsasa90@gmail.com",
            "rainbowkate9@gmail.com",
            "dani91061@freemail.hu",
            "annaflaisz5@gmail.com",
            "hazai65@freemail.hu",
            "amikamina10@gmail.com",
            "biankalaczko03@gmail.com",
            "pako0323@gmail.com",
            "jozsefboross@gmail.com",
            "kittivarga0813@gmail.com",
            "verespanna01@gmail.com",
            "matemagic2004@gmail.com",
            "bogi.gajda1207@gmail.com",
            "takacs.jozsef50@gmail.com",
            "lancerbt@freemail.hu",
            "annamolnar41@gmail.com",
            "fcsaba.77@freemail.hu",
            "medvevivien94@gmail.com",
            "noncsikakas@gmail.com",
            "evetovicseliza12@gmail.com",
            "vercsi0730@gmail.com",
            "jelko1@windowslive.com",
            "imrebanhegyi11@gmail.com",
            "laurahajdu12@gmail.com",
            "75andreacsanyi@gmail.com",
            "sztretye97@freemail.hu",
            "paldomcsika@gmail.com",
            "lorinc402@gmail.com",
            "magyarbella09@gmail.com",
            "topornikol1202@gmail.com",
            "gebri.sara79@gmail.com",
            "romfa.rebeka@gmail.com",
            "battila59@digikabel.hu",
            "nagykarolina0000@gmail.com",
            "levaibernadett1@gmail.com",
            "zsofiao322.rdg@gmail.com",
            "bieliczky.ivettke@gmail.com",
            "berkesmama@citromail.hu",
            "poktamara068@gmail.com",
            "lilike0812@gmail.com",
            "battonyaif@freemail.hu",
            "tothalexa2005@gmail.com",
            "dgyula@tvn.hu",
            "morotzne.eva@gmail.com",
            "bocskorbea@gmail.com",
            "lenart.zsofia65@gmail.com",
            "tandl.zsani@gmail.com",
            "csaba.ciocarlan@gmail.com",
            "karolyiliza@gmail.com",
            "schiffnori@gmail.com",
            "foldesiniki01@gmail.com",
            "kissne.hajnalka@citromail.hu",
            "valek.ametiszt@gmail.com",
            "gota0307@freemail.hu",
            "hegedus.zoltan63@gmail.com",
            "kisbili88@gmail.com",
            "virag.hencsik@freemail.hu",
            "buczkozsanett@citromail.hu",
            "regaaa342@gmail.com",
            "ba007zsi8318@gmail.com",
            "nora.horvath2005@gmail.com",
            "pasztoorgiina@gmail.com",
            "ildoa100@gmail.com",
            "illes630@freemail.hu",
            "fanncsilla@gmail.com",
            "hannaopree@gmail.com",
            "gyorgyne47@gmail.com",
            "kvbrigi922@gmail.com",
            "gyenescsaba@freemail.hu",
            "gyemantalbert@gmail.com",
            "timi.gy@vipmail.hu",
            "nemes_niki@freemail.hu",
            "r.m.s.m.s.0819@gmail.com",
            "rohonyizsuzsa@gmail.com",
            "papcsakjazmin@freemail.hu",
            "kovacs.dorcsika001@gmail.com",
            "tintilus769@gmail.com",
            "tokkamokka75@gmail.com",
            "csicsobarna@freemail.hu",
            "ondrik.tamas02@gmail.com",
            "konczgergo3@gmail.com",
            "matyekvivi@gmail.com",
            "magamarci@gmail.com",
            "mozsiviktor@gmail.com",
            "blilireni@gmail.com",
            "szalayerika77@gmail.com",
            "stemlerjuca@gmail.com",
            "lineage.qkac@gmail.com",
            "nemesreka789@gmail.com",
            "livecraft.hu@gmail.com",
            "eniko19931022@gmail.com",
            "sisabarbi10@gmail.com",
            "erica070453@gmail.com",
            "baronet@citromail.hu",
            "carson9@freemail.hu",
            "gondemare@gmail.hu",
            "hovti@citromail.hu",
            "katika@gmail.com",
            "mikeseva@hotmail.com",
            "oblivion6@freemail.hu",
            "Oszd@freemail.hu",
            "tugyit@freemail.hu",
            "verdier@freemail.hu",
            "fleiszj@gmail.com",
            "sike48@freemail.hu",
            "7000x7000@gmail.com",
            "dorina.10@citromail.hu",
            "easyhd987@gmail.com",
            "marcsi6@gmail.com",
            "szinhaz25@gmail.com",
            "haueva@gmail.com",
            "eenikoe@freemail.hu",
            "gorzolt@gmail.com",
            "raguza64@gmail.com",
            "komplexb@gmail.com",
            "weisedom@gmail.com",
            "nroland89@gmail.com",
            "johnyka72@gmail.com",
            "voros2010@gmail.com",
            "vagyok1972@gmail.com",
            "zsanett0108@gmail.com",
            "tefdek@gmail.com",
            "csogjo@gmail.com",
            "fixzso@gmail.com",
            "bablint@citromail.hu",
            "tolnajb@t-online.hu",
            "harsadel@gmail.com",
            "mara5401@gmail.com",
            "fater845@gmail.com",
            "bogca2000@gmail.com",
            "damon6018@gmail.com",
            "trixi5141@gmail.com",
            "domonyi63@gmail.com",
            "laszlo0614@gmail.com",
            "zoltan1728@gmail.com",
            "andi730113@gmail.com",
            "jozsi.szabo53@gmail.com",
            "szilagyine1952@t-online.hu",
            "hajnalka.kantor@gmail.com",
            "sz.norbert.1996@gmail.com"
        ];

        $this->info('Migrating users...');
        $users = DB::connection('old_mysql')->table('users')->get()->unique('screen_name');
        //$users = new Collection();
        foreach($users as $user) {
            /* Updating model */
            try {
                $emailIndex = array_search($user->email, $hashes, true);

                if($emailIndex) {
                    $user = User::firstOrCreate([
                        'secret_uuid' => Str::uuid(),
                        'status' => $user->status,
                        'username' => $user->screen_name,
                        'password' => Str::random(8),
                        'email' => $emails[$emailIndex],
                        'about' => $user->about,
                        'email_verified_at' => Carbon::createFromTimestamp($user->join_date)->format('Y-m-d H:i:s'),
                        'created_at' => Carbon::createFromTimestamp($user->join_date)->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::createFromTimestamp($user->join_date)->format('Y-m-d H:i:s'),
                        'last_login_at' => $user->last_login,
                    ]);
                } else {
                    $user = User::firstOrCreate([
                        'secret_uuid' => Str::uuid(),
                        'status' => $user->status,
                        'username' => $user->screen_name,
                        'password' => Str::random(8),
                        'email' => $user->email,
                        'about' => $user->about,
                        'email_verified_at' => Carbon::createFromTimestamp($user->join_date)->format('Y-m-d H:i:s'),
                        'created_at' => Carbon::createFromTimestamp($user->join_date)->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::createFromTimestamp($user->join_date)->format('Y-m-d H:i:s'),
                        'last_login_at' => $user->last_login,
                    ]);
                }

                    switch($user->group) {
                        case 1:
                            $roleID = $roleIDs->firstWhere('name', 'administrator')->id;
                            break;
                        case 2:
                            $roleID = $roleIDs->firstWhere('name', 'moderator')->id;
                            break;
                        case 3:
                        default:
                            $roleID = $roleIDs->firstWhere('name', 'user')->id;
                            break;
                        case 4:
                            $roleID = $roleIDs->firstWhere('name', 'uploader')->id;
                            break;
                    }

                    /* User role */
                    UserRole::create([
                        'model_type' => User::class,
                        'model_id' => $user->id,
                        'role_id' => $roleID
                    ]);
            } catch (QueryException $e) {

            }

        }
        /* Stage 4 */

        $this->info('Migrating movies...');

        foreach ($imdbIDs as $imdbID) {
            try {
                $realMovie = Movie::where('imdb_id', $imdbID)->first();
                $movie = DB::connection('old_mysql')->table('movies')->where('imdb_id', $imdbID)->first();


                if ($movie) {
                    /* Updating model */
                    $uploader = DB::connection('old_mysql')->table('users')->where('id', $movie->user_id)->first();

                    $foundInOur = User::where('username', $uploader->screen_name)->first();

                    $array = [
                        'status' => (string)$movie->enabled,
                        'type' => $movie->type - 1,
                        'is_premier' => $movie->premier,
                        'user_id' => $foundInOur ? ($foundInOur->id) : null,
                        'porthu_id' => $movie->porthu,

                        'created_at' => Carbon::createFromTimestamp($movie->add_date)->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::createFromTimestamp($movie->add_date)->format('Y-m-d H:i:s'),
                        'accepted_at' => Carbon::createFromTimestamp($movie->accepted_at)->format('Y-m-d H:i:s'),
                    ];
                    $realMovie->update($array);

                    /* Inserting youtube videos */
                    if ($movie->youtube) {
                        MovieVideo::create([
                            'movie_id' => $realMovie->id,
                            'youtube_id' => $movie->youtube,
                            'status' => '1'
                        ]);
                    }

                    /* Inserting links */
                    $links = DB::connection('old_mysql')->table('movie_links')->where('movie_id', $movie->id)->get();
                    foreach ($links as $link) {
                        $siteName = DB::connection('old_mysql')->table('movie_sites')->where('id', $link->site_id)->first() ? DB::connection('old_mysql')->table('movie_sites')->where('id', $link->site_id)->first()->name : null;
                        $quality = DB::connection('old_mysql')->table('movie_qualities')->where('id', $link->quality)->first() ? DB::connection('old_mysql')->table('movie_qualities')->where('id', $link->quality)->first()->name : null;
                        $lang = DB::connection('old_mysql')->table('link_langs')->where('id', $link->lang)->first() ? DB::connection('old_mysql')->table('link_langs')->where('id', $link->lang)->first()->name : null;

                        $userName = DB::connection('old_mysql')->table('users')->where('id', $link->user_id)->first() ? DB::connection('old_mysql')->table('users')->where('id', $link->user_id)->first()->screen_name : null;

                        switch ($quality) {
                            case 'TV':
                                $linkTypeID = LinkType::where('name', 'tv')->first()->id;
                                break;
                            case 'TC':
                                $linkTypeID = LinkType::where('name', 'tc')->first()->id;
                                break;
                            case 'HD':
                                $linkTypeID = LinkType::where('name', 'hd')->first()->id;
                                break;
                            case 'CAM':
                                $linkTypeID = LinkType::where('name', 'cam')->first()->id;
                                break;
                            case 'HC-HDRip':
                                $linkTypeID = LinkType::where('name', 'hc-hdrip')->first()->id;
                                break;
                            case 'TS':
                                $linkTypeID = LinkType::where('name', 'ts')->first()->id;
                                break;
                            case 'DVD-mozis hang':
                                $linkTypeID = LinkType::where('name', 'dvd-cinema')->first()->id;
                                break;
                            case 'DVD':
                            default:
                                $linkTypeID = LinkType::where('name', 'dvd')->first()->id;
                                break;
                        }

                        switch ($lang) {
                            default:
                            case 'magyar hang':
                                $langID = LanguageType::where('name', 'hu')->first()->id;
                                break;
                            case 'angol hang':
                                $langID = LanguageType::where('name', 'en')->first()->id;
                                break;
                            case 'magyar felirat':
                                $langID = LanguageType::where('name', 'sub')->first()->id;
                                break;
                            case 'egyéb':
                                $langID = LanguageType::where('name', 'other')->first()->id;
                                break;
                        }

                        MovieLink::create([
                            'movie_id' => $realMovie->id,
                            'status' => $link->enabled,
                            'link' => $link->link,
                            'site_id' => $siteName === null ? null : Site::where('name', mb_strtoupper($siteName))->first()->id,
                            'link_type_id' => $linkTypeID,
                            'language_type_id' => $langID,
                            'part' => $link->part,
                            'season' => $link->season,
                            'episode' => $link->episode,
                            'user_id' => $foundInOur === null ? null : $foundInOur->id,
                            'created_at' => Carbon::createFromTimestamp($link->add_date)->format('Y-m-d H:i:s'),
                            'updated_at' => Carbon::createFromTimestamp($link->add_date)->format('Y-m-d H:i:s'),
                        ]);
                    }

                    /* Inserting comments */
                    $comments = DB::connection('old_mysql')->table('comments')->where('movie_id', $movie->id)->get();
                    foreach ($comments as $comment) {
                        $userName = DB::connection('old_mysql')->table('users')->where('id', $comment->user_id)->first()->screen_name;

                        $ourUser = User::where('username', $userName)->first();

                        if($ourUser) {
                            MovieComment::create([
                                'status' => $comment->enabled,
                                'movie_id' => $realMovie->id,
                                'user_id' => $ourUser->id,
                                'comment' => $comment->text,
                                'created_at' => Carbon::createFromTimestamp($comment->post_time),
                                'updated_at' => Carbon::createFromTimestamp($comment->post_time),
                            ]);
                        }
                    }

                    /* Inserting favourites */
                    $favourites = DB::connection('old_mysql')->table('user_favorites')->where('movie_id', $movie->id)->get();
                    foreach ($favourites as $favourite) {
                        $userName = DB::connection('old_mysql')->table('users')->where('id', $favourite->user_id)->first()->screen_name;

                        $ourUser = User::where('username', $userName)->first();

                        if($ourUser) {
                            MovieFavourite::create([
                                'user_id' => $ourUser->id,
                                'movie_id' => $realMovie->id
                            ]);
                        }
                    }

                    /* Inserting to be watched */
                    $watcheds = DB::connection('old_mysql')->table('user_watch')->where('movieID', $movie->id)->get();
                    foreach ($watcheds as $watched) {
                        $userName = DB::connection('old_mysql')->table('users')->where('id', $watched->userID)->first()->screen_name;

                        $ourUser = User::where('username', $userName)->first();

                        if($ourUser) {
                            MovieToBeWatched::create([
                                'user_id' => $ourUser->id,
                                'movie_id' => $realMovie->id
                            ]);
                        }
                    }

                    /* Inserting user ratings */
                    $ratings = DB::connection('old_mysql')->table('user_votes')->where('movie_id', $movie->id)->get();
                    foreach ($ratings as $rating) {
                        $userName = DB::connection('old_mysql')->table('users')->where('id', $rating->user_id)->first()->screen_name;

                        $ourUser = User::where('username', $userName)->first();

                        if($ourUser) {
                            MovieRating::create([
                                'user_id' => $ourUser->id,
                                'movie_id' => $realMovie->id,
                                'rating' => $rating->vote
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                dd($e);
                $this->error('Exception:' . $e->getMessage());
            }
        }

        dd($imdbIDs);
    }
}

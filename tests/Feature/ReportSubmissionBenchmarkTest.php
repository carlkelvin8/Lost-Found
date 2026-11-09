<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportSubmissionBenchmarkTest extends TestCase
{
    use DatabaseMigrations;

    public function test_measure_committed_submission_with_controlled_mail_latency(): void
    {
        if (! getenv('RUN_REPORT_BENCHMARK')) {
            $this->markTestSkipped('Opt-in benchmark.');
        }
        Storage::fake('public');
        config(['filesystems.uploads' => 'public', 'mail.default' => 'array', 'services.openai.api_key' => null]);
        Event::listen(MessageSending::class, fn () => usleep(200000));
        for ($i = 0; $i < 3; $i++) {
            $staff = User::factory()->create();
            $staff->roles()->firstOrCreate(['name' => 'admin']);
        }
        $category = Category::create(['name' => 'Bags']);
        $location = Location::create(['name' => 'Building A']);
        $this->actingAs(User::factory()->create());
        $samples = [];
        for ($i = 0; $i < 6; $i++) {
            $payload = ['submission_id' => (string) Str::uuid(), 'report_type' => 'lost', 'item_name' => 'Blue bag',
                'item_description' => 'A distinctive blue bag with a silver strap.', 'color' => 'Blue',
                'category_id' => $category->id, 'location_id' => $location->id];
            for ($j = 0; $j < 5; $j++) {
                $payload['photos'][] = UploadedFile::fake()->image("photo{$j}.jpg", 2000, 1500);
            }
            DB::enableQueryLog();
            DB::flushQueryLog();
            $start = hrtime(true);
            $this->postJson(route('reports.store'), $payload)->assertCreated();
            $elapsed = (hrtime(true) - $start) / 1e6;
            if ($i > 0) {
                $samples[] = ['ms' => round($elapsed, 2), 'queries' => count(DB::getQueryLog())];
            }
            DB::disableQueryLog();
        }
        fwrite(STDOUT, "\nREPORT_BENCHMARK ".json_encode($samples)."\n");
    }
}

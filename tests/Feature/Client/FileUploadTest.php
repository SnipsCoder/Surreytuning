<?php

namespace Tests\Feature\Client;

use App\Enums\TuningToolCategory;
use App\Enums\UserRole;
use App\Enums\VehicleType;
use App\Models\Dealer;
use App\Models\FileRequest;
use App\Models\FileStage;
use App\Models\TuningTool;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    private function clientUser(?Dealer $dealer = null): User
    {
        $dealer ??= Dealer::factory()->create();

        return User::factory()->create([
            'role' => UserRole::DealerOwner,
            'dealer_id' => $dealer->id,
        ]);
    }

    private function fileStage(): FileStage
    {
        return FileStage::create([
            'name' => 'Stage 1 Remap',
            'description' => 'Stage 1',
            'vehicle_type' => VehicleType::Car,
            'price_net' => 100,
            'vat_applicable' => true,
            'turnaround_hours' => 24,
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function tuningTool(): TuningTool
    {
        return TuningTool::create([
            'name' => 'Autotuner OBD',
            'category' => TuningToolCategory::Obd,
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_upload_create_shows_file_type_torque_and_ecu_model_fields(): void
    {
        $user = $this->clientUser();

        $response = $this->actingAs($user)->get('/my/upload');

        $response->assertOk();
        $response->assertSee('file_type', false);
        $response->assertSee('torque_before_nm', false);
        $response->assertSee('ecu_model_no', false);
    }

    public function test_upload_store_persists_file_type_torque_and_ecu_model(): void
    {
        Storage::fake('r2');

        $dealer = Dealer::factory()->create();
        $user = $this->clientUser($dealer);
        $stage = $this->fileStage();
        $tool = $this->tuningTool();

        $response = $this->actingAs($user)->post('/my/upload', [
            'make' => 'BMW',
            'model' => '320d',
            'year' => 2020,
            'engine' => '2.0d',
            'fuel' => 'diesel',
            'transmission' => 'automatic',
            'file_stage_id' => $stage->id,
            'tool_id' => $tool->id,
            'file' => UploadedFile::fake()->create('tune.bin', 100),
            'file_type' => 'tcu',
            'torque_before_nm' => 350.5,
            'ecu_model_no' => 'EDC17C46',
        ]);

        $fileRequest = FileRequest::where('dealer_id', $dealer->id)->latest('id')->first();

        $this->assertNotNull($fileRequest);
        $response->assertRedirect(route('client.file-requests.show', $fileRequest));
        $this->assertSame('tcu', $fileRequest->file_type->value ?? $fileRequest->file_type);
        $this->assertEquals(350.5, $fileRequest->torque_before_nm);
        $this->assertSame('EDC17C46', $fileRequest->ecu_model_no);
    }

    public function test_upload_create_excludes_electric_and_hybrid_fuel_options(): void
    {
        $user = $this->clientUser();

        $response = $this->actingAs($user)->get('/my/upload');

        $response->assertOk();
        $response->assertSee('value="petrol"', false);
        $response->assertSee('value="diesel"', false);
        $response->assertDontSee('value="electric"', false);
        $response->assertDontSee('value="hybrid"', false);
    }

    public function test_upload_store_rejects_electric_fuel_type(): void
    {
        Storage::fake('r2');

        $dealer = Dealer::factory()->create();
        $user = $this->clientUser($dealer);
        $stage = $this->fileStage();
        $tool = $this->tuningTool();

        $response = $this->actingAs($user)->post('/my/upload', [
            'make' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2020,
            'engine' => 'Electric',
            'fuel' => 'electric',
            'transmission' => 'automatic',
            'file_stage_id' => $stage->id,
            'tool_id' => $tool->id,
            'file' => UploadedFile::fake()->create('tune.bin', 100),
            'file_type' => 'ecu',
        ]);

        $response->assertSessionHasErrors('fuel');
    }

    public function test_upload_store_persists_adblue_file_type(): void
    {
        Storage::fake('r2');

        $dealer = Dealer::factory()->create();
        $user = $this->clientUser($dealer);
        $stage = $this->fileStage();
        $tool = $this->tuningTool();

        $response = $this->actingAs($user)->post('/my/upload', [
            'make' => 'BMW',
            'model' => '320d',
            'year' => 2020,
            'engine' => '2.0d',
            'fuel' => 'diesel',
            'transmission' => 'automatic',
            'file_stage_id' => $stage->id,
            'tool_id' => $tool->id,
            'file' => UploadedFile::fake()->create('tune.bin', 100),
            'file_type' => 'adblue',
        ]);

        $fileRequest = FileRequest::where('dealer_id', $dealer->id)->latest('id')->first();

        $this->assertNotNull($fileRequest);
        $response->assertRedirect(route('client.file-requests.show', $fileRequest));
        $this->assertSame('adblue', $fileRequest->file_type->value ?? $fileRequest->file_type);
    }
}

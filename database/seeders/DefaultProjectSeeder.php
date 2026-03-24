<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project = \App\Models\Project::firstOrCreate(
        ['name' => 'Agate Tower project'],
        ['description' => 'Default project containing all existing pre-multi-project data.']
        );

        \App\Models\Building::whereNull('project_id')->update(['project_id' => $project->id]);
        \App\Models\BoqCsvUpload::whereNull('project_id')->update(['project_id' => $project->id]);
        \App\Models\Material::whereNull('project_id')->update(['project_id' => $project->id]);
        \App\Models\Tool::whereNull('project_id')->update(['project_id' => $project->id]);
        \App\Models\ObjectComponent::whereNull('project_id')->update(['project_id' => $project->id]);
    }
}
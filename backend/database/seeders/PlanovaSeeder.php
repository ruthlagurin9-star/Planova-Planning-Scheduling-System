<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class PlanovaSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo users. Re-running this seeder won't duplicate them.
        $admin = User::firstOrCreate(
            ['email' => 'admin@planova.test'],
            [
                'name' => 'Planova Administrator',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'user@planova.test'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make('User123!'),
                'role' => 'user',
            ]
        );

        // Create categories.
        $categoryNames = [
            'Work',
            'Education',
            'Personal',
            'Meetings',
            'Projects',
        ];

        $categories = [];

        foreach ($categoryNames as $name) {
            $categories[$name] = Category::firstOrCreate(
                ['name' => $name],
                ['description' => $name . ' activities and schedules']
            );
        }

        // Create ten sample schedules.
        $scheduleTitles = [
            'Team Planning Meeting',
            'Database Design',
            'React Development',
            'Laravel API Development',
            'Project Documentation',
            'UI Design Review',
            'Testing and Debugging',
            'Weekly Progress Review',
            'Client Presentation Preparation',
            'Project Final Review',
        ];

        $scheduleCategories = [
            'Meetings', 'Education', 'Projects', 'Projects',
            'Work', 'Work', 'Projects', 'Meetings',
            'Education', 'Projects',
        ];

        $schedules = [];

        foreach ($scheduleTitles as $index => $title) {
            $start = Carbon::now()
                ->addDays($index + 1)
                ->setTime(9, 0);

            $schedules[] = Schedule::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $title,
                ],
                [
                    'category_id' => $categories[
                        $scheduleCategories[$index]
                    ]->id,
                    'description' => 'Planova sample activity: ' . $title,
                    'start_at' => $start,
                    'end_at' => $start->copy()->addHour(),
                    'status' => $index < 2
                        ? 'pending'
                        : ($index < 5 ? 'in_progress' : 'completed'),
                ]
            );
        }

        // Create twenty sample tasks, two for each schedule.
        foreach ($schedules as $index => $schedule) {
            foreach ([1, 2] as $number) {
                $taskTitle = 'Task ' . $number . ': ' . $scheduleTitles[$index];

                Task::firstOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'title' => $taskTitle,
                    ],
                    [
                        'assigned_to' => $user->id,
                        'description' => 'Complete the assigned work and update its progress.',
                        'due_date' => Carbon::today()->addDays(
                            $index + $number + 1
                        ),
                        'priority' => $number === 1 ? 'high' : 'medium',
                        'status' => $index < 2
                            ? 'pending'
                            : ($index < 5 ? 'in_progress' : 'completed'),
                    ]
                );
            }
        }

        // Create sample activity logs.
        ActivityLog::firstOrCreate(
            [
                'user_id' => $admin->id,
                'action' => 'demo_setup',
                'description' => 'Initial Planova demonstration data prepared.',
            ]
        );

        ActivityLog::firstOrCreate(
            [
                'user_id' => $user->id,
                'action' => 'schedule_created',
                'description' => 'Sample schedules and tasks prepared.',
            ]
        );
    }
}

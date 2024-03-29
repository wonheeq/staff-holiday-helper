<?php

namespace Tests\Unit;

use App\Jobs\EmailExceptionTestJob;
use App\Jobs\SendAppCancelledManager;
use App\Jobs\SendApplicationDecision;
use App\Jobs\SendAppWaitingRev;
use App\Jobs\SendConfirmSubstitutions;
use App\Jobs\SendNominationCancelled;
use App\Jobs\SendNominationDeclined;
use App\Jobs\SendNominationEmail;
use App\Jobs\SendNominationsCancelled;
use App\Jobs\SendNomineeAppEdited;
use App\Jobs\SendSubPeriodEditSubset;
use App\Jobs\SendSystemNotification;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Models\Account;
use App\Models\UnsentEmail;

use function PHPUnit\Framework\assertTrue;

class JobsTest extends TestCase
{
    private Account $user, $adminUser;

    protected function setup(): void
    {
        parent::setup();
        // Create test data
        $this->user = Account::factory()->create();
        $this->adminUser = Account::factory()->create([
            'accountType' => "sysadmin"
        ]);
    }

    protected function teardown(): void
    {
        $this->user->delete();
        $this->adminUser->delete();
        parent::teardown();
    }


    // public function test_SendAppCancelledManager_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string",];
    //     $data = [$this->adminUser->accountNo, $content, $this->user->accountNo,];
    //     $job = new SendAppCancelledManager($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_sendApplicationDecision_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string"];
    //     $data = [$this->adminUser->accountNo, $content];
    //     $job = new SendApplicationDecision($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendAppWaitingRev_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string"];
    //     $data = [$this->adminUser->accountNo, $this->user->accountNo, $content];
    //     $job = new SendAppWaitingRev($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendConfirmSubstitutions_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string", "test string"];
    //     $data = [$this->adminUser->accountNo, $content,];
    //     $job = new SendConfirmSubstitutions($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendNominationCancelled_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string"];
    //     $data = [$this->adminUser->accountNo, $content, $this->user->accountNo];
    //     $job = new SendNominationCancelled($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendNominationDeclined_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string", "test string", "test string",];
    //     $data = [$this->adminUser->accountNo, $content,];
    //     $job = new SendNominationDeclined($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendNominationEmail_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string"];
    //     $data = [$this->adminUser->accountNo, $content,];
    //     $job = new SendNominationEmail($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendNominationsCancelled_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string", "test string"];
    //     $data = [$this->adminUser->accountNo, $content,];
    //     $job = new SendNominationsCancelled($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendNomineeAppEdited_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string", "test string", "test string",];
    //     $data = [$this->adminUser->accountNo, $content,];
    //     $job = new SendNomineeAppEdited($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendSubPeriodEditSubset_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string", "test string", "test string", "test string",];
    //     $data = [$this->adminUser->accountNo, $content,];
    //     $job = new SendSubPeriodEditSubset($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


    // public function test_SendSystemNotification_sends(): void
    // {
    //     Mail::fake();
    //     Mail::assertNothingQueued();
    //     $content = ["test string"];
    //     $data = [$this->adminUser->accountNo, $content,];
    //     $job = new SendSystemNotification($data);
    //     $job->handle();
    //     Mail::assertQueuedCount(1);
    // }


}

<?php namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Northstar\Models\User;

class RemoveDuplicateUsersCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'users:dedupe';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Script to remove duplicate users.';

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
	 * @return mixed
	 */
	public function fire()
	{
        // Get all the users.
		$users = User::all();

        $duplicates = User::select('email')->havingRaw('count(*) > 1')->get()->toArray();
        dd($duplicates);
        // Put all user emails into an array.
        $emails = User::select('email')->get()->toArray();
        $emails_array = array();

        foreach ($emails as $email) {
            if(isset($email['email'])) {
                $emails_array[] = $email['email'];
            }
        }

        // Put all user mobile numbers into an array.
        // TODO: Finish this section, possible add line to convert all mobile numbers to same format.
        $mobile_numbers = User::select('mobile')->get()->toArray();

        // Find all duplicate emails and put into new array.
        $emails_count_array = array_count_values($emails_array);
        $dups = array();

        foreach ($emails_count_array as $key => $count) {
            if ($count > 1) {
                $dups[] = $key;
            }
        }

        // For each duplicate user, delete record most recently updated at.
        // TODO: Add if block to account for emails that have been added more than twice.
        foreach ($dups as $key => $email) {
            User::where('email', '=', $email)->orderBy('updated_at', 'DESC')->first()->delete();
        }
        $this->info('Deduplication complete.');
	}
}

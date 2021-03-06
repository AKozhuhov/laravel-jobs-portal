<?php
/**
 * Created by PhpStorm.
 * User: andrestntx
 * Date: 5/21/16
 * Time: 7:37 PM
 */

namespace App\Services;

use App\Entities\Application;
use App\Entities\Company;
use App\Entities\Job;
use App\Entities\Resume;
use App\Entities\User;
use App\Repositories\ParameterRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    protected $fromEmail = 'noresponder@uncorreo.co';
    protected $fromName = 'Portal de empleo';
    protected $parameterRepository;
    protected $cc = 'andres@dondepauto.co';


    /**
     * EmailService constructor.
     * @param ParameterRepository $parameterRepository
     */
    public function __construct(ParameterRepository $parameterRepository)
    {
        //$this->fromName = $parameterRepository->getValue('portal_nombre');
        $this->fromName = env('MAIL_FROM', 'noreplay@empleoenplanas.com');
        $this->fromEmail = $parameterRepository->getValue('empresa_email');
        $this->cc = env('MAIL_CC', 'contacto@empleoenplanas.com');
    }


    /**
     * @param Resume $resume
     * @param Job $job
     * @param Application $application
     * @param $pathToFile
     */
    public function sendResume(Resume $resume, Job $job, Application $application, $pathToFile)
    {
        $fromEmail = $this->fromEmail;
        $fromName = $this->fromName;
        $cc = $this->cc;
        /*if($job->company->email_new_application) {
            Mail::send('emails.apply-company', ['resume' => $resume, 'job' => $job, 'application' => $application], function ($m) use ($job, $resume, $fromEmail, $fromName, $pathToFile) {
                $m->from($fromEmail, $fromName);
                $m->to($job->email, $job->company->name)
                    ->subject('Haz recibido una hoja de vida de ' . $resume->jobseeker->full_name)
                    ->cc(self::$cc)
                    ->attach(url($pathToFile));
            });
        }*/

        Mail::send('emails.apply-jobseeker', ['resume' => $resume, 'job' => $job], function ($m) use ($job, $resume, $fromEmail, $fromName) {
            $m->from($fromEmail, $fromName);
            $m->to($resume->jobseeker->email, $resume->jobseeker->full_name)
                ->subject('Ha sido enviada su hoja de vida a la empresa ' . $job->company->name)
                ->cc($cc);
        });
    }

    /**
     * @param User $user
     */
    public function welcomeUser(User $user)
    {
        $fromEmail = $this->fromEmail;
        $fromName = $this->fromName;
        $cc = $this->cc;

        Mail::send('emails.welcome', ['user' => $user], function ($m) use ($user, $fromEmail, $fromName, $cc) {
            $m->from($fromEmail, $fromName);
            $m->to($user->email, $user->full_name)
                ->subject('Bienvenido ' . $user->name)
                ->cc($cc);
        });
    }

    /**
     * @param Job $job
     */
    public function notifyNewJob(Job $job)
    {
        $fromEmail = $this->fromEmail;
        $fromName = $this->fromName;
        $cc = $this->cc;
        $user = $job->company->user;

        Mail::send('emails.notify-new-job', ['job' => $job], function ($m) use ($user, $job, $fromEmail, $fromName, $cc) {
            $m->from($fromEmail, $fromName);
            $m->to($job->email, $user->name)
                ->subject('Ha creado una nueva oferta de empleo con éxito')
                ->cc($cc);
        });
    }

    /**
     * @param User $user
     */
    public function notifyActiveUser(User $user)
    {
        $fromEmail = $this->fromEmail;
        $fromName = $this->fromName;
        $cc = $this->cc;

        Mail::send('emails.notify-active-user', ['user' => $user], function ($m) use ($user, $fromEmail, $fromName, $cc) {
            $m->from($fromEmail, $fromName);
            $m->to($user->email, $user->name)
                ->subject('Su cuenta ha sido activada')
                ->cc($cc);
        });
    }

    /**
     * @param User $user
     * @param Collection $admins
     */
    public function notifyNewUser(User $user, Collection $admins)
    {
        $fromEmail = $this->fromEmail;
        $fromName = $this->fromName;
        $cc = $this->cc;

        Mail::send('emails.notify-new-user', ['user' => $user], function ($m) use ($user, $fromEmail, $fromName, $admins, $cc) {
            $m->from($fromEmail, $fromName);

            foreach($admins as $admin) {
                $m->to($admin->email, $admin->name)
                    ->subject('Nuevo usuario registrado: ' . $user->name);
            }
        });
    }

    /**
     * @param Job $job
     * @param $count
     */
    public function notifyPreselect(Job $job, $count)
    {
        $fromEmail = $this->fromEmail;
        $fromName = $this->fromName;
        $cc = $this->cc;

        Mail::send('emails.notify-preselect', ['job' => $job, 'count' => $count], function ($m) use ($job, $fromEmail, $fromName, $cc) {
            $m->from($fromEmail, $fromName)
                ->to($job->email, $job->name)
                ->cc($cc)
                ->subject('Preselección finalizada de ' . $job->name);
        });
    }
}
<?php

namespace App\Http\Controllers;

class ResourceController extends Controller
{
    private function fromPublicPath($file_path, $headers = [])
    {
        // https://bugs.php.net/bug.php?id=53035
        if (substr($file_path, -3) === '.js') {
            $headers['content-type'] = 'application/javascript';
        } elseif (substr($file_path, -4) === '.css') {
            $headers['content-type'] = 'text/css';
        }

        return response()->file(public_path().$file_path, $headers);
    }

    public function questions()
    {
        return $this->fromPublicPath('/js/questions.js');
    }

    public function startSurvey()
    {
        return $this->fromPublicPath('/js/start-survey.js');
    }

    public function js()
    {
        return $this->fromPublicPath('/js/app.js');
    }

    public function manageSurvey()
    {
        return $this->fromPublicPath('/js/manage-survey.js');
    }

    public function stats()
    {
        return $this->fromPublicPath('/js/stats.js');
    }

    public function css()
    {
        return $this->fromPublicPath('/css/app.css', [
            'content-type' => 'text/css',
        ]);
    }

    public function fonts($font_file)
    {
        return $this->fromPublicPath('/fonts/'.$font_file);
    }

    public function jpgImages($image_file)
    {
        return $this->fromPublicPath('/images/'.$image_file, [
            'content-type' => 'image/jpg',
        ]);
    }
}

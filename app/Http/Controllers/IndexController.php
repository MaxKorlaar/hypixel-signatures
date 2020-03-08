<?php

    namespace App\Http\Controllers;

    use Illuminate\Contracts\View\Factory;
    use Illuminate\View\View;

    /**
     * Class IndexController
     *
     * @package App\Http\Controllers
     */
    class IndexController extends Controller {
        /**
         * @return Factory|View
         */
        public function index() {
            return view('index');
        }
    }

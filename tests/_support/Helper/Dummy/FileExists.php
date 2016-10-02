<?php

namespace Cheppers\Robo\Git\Task {

    class Helper
    {

        /**
         * @var bool[]
         */
        public static $fileExistsReturnValues = [];
    }

    function file_exists()
    {
        return array_shift(Helper::$fileExistsReturnValues);
    }
}

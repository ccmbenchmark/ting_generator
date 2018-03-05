<?php
/***********************************************************************
 *
 * Ting Generator
 * ==========================================
 *
 * Copyright (C) 2017 CCM Benchmark Group. (http://www.ccmbenchmark.com)
 *
 ***********************************************************************
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you
 * may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 **********************************************************************/

namespace CCMBenchmark\TingGenerator\Infrastructure;

use Psr\Log\LoggerInterface;

/**
 * Class PSR2Fixer
 * @package CCMBenchmark\TingGenerator\Infrastructure
 * @tags PSR2Fixer
 */
class PSR2Fixer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PSR2Fixer constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $filename
     *
     * @return boolean
     */
    public function fix($filename)
    {
        $filename = (string) $filename;

        if (file_exists($filename) === false) {
            $this->logger->error('File not found: ' . $filename);
            return false;
        }

        $fileContent = file_get_contents($filename);
        if ($fileContent === false) {
            $this->logger->error('Unable to get content of file: ' . $filename);
            return false;
        }

        $fileContent = $this->removeInvalidBlankLinesAtEndOfFile($fileContent);
        $fileContent = $this->removeInvalidBlankLineAtStartOfClass($fileContent);

        if (file_put_contents($filename, $fileContent) === false) {
            $this->logger->error('Unable to write file: ' . $filename);
            return false;
        }

        return true;
    }

    /**
     * @param string $fileContent
     *
     * @return string
     */
    private function removeInvalidBlankLinesAtEndOfFile($fileContent)
    {
        return preg_replace(
            '#}\n\n\n}\n\n#',
            "}\n}\n",
            $fileContent
        );
    }

    /**
     * @param string $fileContent
     *
     * @return string
     */
    private function removeInvalidBlankLineAtStartOfClass($fileContent)
    {
        return preg_replace_callback(
            '#(?<class>class[^{]*{)\n\n#',
            function ($match) {
                return $match['class'] . "\n";
            },
            $fileContent
        );
    }
}

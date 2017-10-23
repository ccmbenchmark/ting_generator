<?php
/***********************************************************************
 *
 * Ting Generator
 * ==========================================
 *
 * Copyright (C) 2014 CCM Benchmark Group. (http://www.ccmbenchmark.com)
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

namespace CCMBenchmark\TingGenerator\Entity;

use CCMBenchmark\TingGenerator\Log\Logger;
use Zend\Code\Generator\ClassGenerator;

class Generator
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Generator constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getEntityCodeFromEntityDescription($entityName, $namespace, array $entityDescription)
    {
        $classGenerator = new ClassGenerator();

        $this
            ->setClassHead($classGenerator, $entityName, $namespace)
            ->setClassBody($classGenerator, $entityDescription);
    }

    /**
     * @param ClassGenerator $classGenerator
     * @param string $entityName
     * @param string $namespace
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     *
     * @return $this
     */
    private function setClassHead(ClassGenerator $classGenerator, $entityName, $namespace)
    {
        $classGenerator
            ->setName($entityName)
            ->setNamespaceName($namespace)
            ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface')
            ->addUse('CCMBenchmark\\Ting\\Entity\\NotifyProperty')
            ->setImplementedInterfaces(['CCMBenchmark\\Ting\\Entity\\NotifyPropertyInterface'])
            ->addTrait('NotifyProperty');

        return $this;
    }

    private function setClassBody(ClassGenerator $classGenerator, array $entityDescription)
    {
        foreach ($entityDescription as $field) {
            
        }

        return $this;
    }
}

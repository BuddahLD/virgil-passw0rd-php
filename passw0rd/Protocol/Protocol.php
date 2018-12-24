<?php
/**
 * Copyright (C) 2015-2018 Virgil Security Inc.
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *     (1) Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *     (2) Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *     (3) Neither the name of the copyright holder nor the names of its
 *     contributors may be used to endorse or promote products derived from
 *     this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ''AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Lead Maintainer: Virgil Security Inc. <support@virgilsecurity.com>
 */

namespace passw0rd\Protocol;

use GuzzleHttp\Psr7\Response;
use passw0rd\Exeptions\ProtocolException;
use passw0rd\Exeptions\RequestClassException;
use passw0rd\Helpers\ArrayHelperTrait;
use passw0rd\Helpers\ClassHelperTrait;
use passw0rd\Http\HttpClient;

class Protocol implements AvailableProtocol
{
    use ArrayHelperTrait, ClassHelperTrait;

    const REQUEST_CLASS_NAMESPACE = "passw0rd\\Http\\Request\\";
    const REQUEST_CLASS_SUFFIX= "Request";

    private $context;
    private $httpClient;

    /**
     * Protocol constructor.
     * @param ProtocolContext $context
     */
    public function __construct(ProtocolContext $context)
    {
        $this->context = $context;
        $this->httpClient = new HttpClient();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return Response
     * @throws ProtocolException
     * @throws RequestClassException
     */
    public function __call(string $name, array $arguments): Response
    {
        if(!in_array($name, AvailableProtocol::ENDPOINTS))
            throw new ProtocolException("Incorrect endpoint: $name. Correct endpoints: {$this->toString(AvailableProtocol::ENDPOINTS)}");

        $class = $this->toClass(self::REQUEST_CLASS_NAMESPACE, $name, self::REQUEST_CLASS_SUFFIX);

        if(!$this->isClassExists($class))
            throw new RequestClassException("Class do not exists: $class");

        $this->httpClient->setRequest(new $class($name));
        $response = $this->httpClient->getResponse(true);

        return $response;
    }
}
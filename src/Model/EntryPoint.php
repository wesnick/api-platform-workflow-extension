<?php

declare(strict_types=1);

/*
 * (c) 2019, Wesley O. Nichols
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Wesnick\WorkflowBundle\Model;

use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An entry point, within some Web-based protocol.
 *
 * @see http://schema.org/EntryPoint Documentation on Schema.org
 *
 * @author Wesley O. Nichols <spanishwes@gmail.com>
 */
class EntryPoint
{
    /**
     * @var string|null An HTTP method that specifies the appropriate HTTP method for a request to an HTTP EntryPoint. Values are capitalized strings as used in HTTP.
     *
     * @ApiProperty(iri="http://schema.org/httpMethod")
     * @Assert\Type(type="string")
     */
    private $httpMethod;

    /**
     * @var string|null an url template (RFC6570) that will be used to construct the target of the execution of the action
     *
     * @ApiProperty(iri="http://schema.org/urlTemplate")
     * @Assert\Type(type="string")
     */
    private $urlTemplate;

    /**
     * @var string|null URL of the item
     *
     * @ApiProperty(iri="http://schema.org/url")
     * @Assert\Url
     */
    private $url;

    public function setHttpMethod(?string $httpMethod): void
    {
        $this->httpMethod = $httpMethod;
    }

    public function getHttpMethod(): ?string
    {
        return $this->httpMethod;
    }

    public function setUrlTemplate(?string $urlTemplate): void
    {
        $this->urlTemplate = $urlTemplate;
    }

    public function getUrlTemplate(): ?string
    {
        return $this->urlTemplate;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}

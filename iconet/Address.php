<?php

namespace Iconet;

use InvalidArgumentException;

class Address
{

    public const ENDPOINT = "iconet"; //Path for the iconet API that is appended to the domain part
    private const SEPARATOR = '@';
    private const PATTERN =
        "/^(?<local>[a-zA-Z][\w\.:-]*)" . self::SEPARATOR . "(?<domain>[a-zA-Z0-9-\.:]+)$/";

    public readonly string $local;
    public readonly string $domain;
    public readonly bool $isInternal;

    public function __construct(string $address)
    {
        $parts = self::parse($address);
        if(!$parts) {
            throw new InvalidArgumentException("Wrong address format: '$address'");
        }
        $this->local = $parts['local'];
        $this->domain = $parts['domain'];
        $this->isInternal = self::isInternal($this->domain);
    }

    public static function fromUsername(string $username): ?Address
    {
        $address = $username . self::SEPARATOR . $_ENV['DOMAIN'];
        if(!self::validate($address)) {
            return null;
        }
        return new Address($address);
    }

    /**
     * @param string $address
     * @param bool $onlyInternal When true: Only allow addresses on this server
     * @return bool True, if the address format is valid
     */
    public static function validate(string $address, bool $onlyInternal = false): bool
    {
        return self::parse($address, $onlyInternal) != false;
    }

    /**
     * @param string $address
     * @param bool $onlyInternal When true: Only allow addresses on this server
     * @return bool|array<string> False or matched local/domain parts of the address
     */
    private static function parse(string $address, bool $onlyInternal = false): bool|array
    {
        $matchCount = preg_match(self::PATTERN, $address, $matches);
        $success =
            $matchCount === 1
            && (self::isDomain($matches['domain']))
            && (!$onlyInternal || self::isInternal($matches['domain']));

        return $success ? $matches : false;
    }

    private static function isInternal(string $domain): bool
    {
        return $domain === $_ENV['DOMAIN'];
    }

    //TODO be stricter
    private static function isDomain(string $domain): bool
    {
        return filter_var("https://" . $domain, FILTER_VALIDATE_URL);
    }

    public function getEndpoint(): string
    {
        $schema = $_ENV['DEBUG_DISABLE_HTTPS'] ? "http://" : "https://";
        return $schema . $this->domain . '/' . self::ENDPOINT . '/';
    }

    public function __toString(): string
    {
        return $this->local . self::SEPARATOR . $this->domain;
    }
}
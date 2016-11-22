<?php namespace nyx\auth\id\interfaces;

// Internal dependencies
use nyx\auth;

/**
 * Identity Provider Interface
 *
 * @package     Nyx\Auth
 * @version     0.1.0
 * @author      Michal Chojnacki <m.chojnacki@muyo.io>
 * @copyright   2012-2017 Nyx Dev Team
 * @link        https://github.com/unyx/nyx
 */
interface Provider
{
    /**
     * The authentication endpoint URIs of this Provider.
     */
    const URL_AUTHORIZE = null;
    const URL_EXCHANGE  = null;
    const URL_IDENTIFY  = null;

    /**
     * The Identity class associated with this Provider.
     */
    const IDENTITY = null;

    /**
     * Prepares the authorization parameters necessary to properly redirect the end-user of the application
     * to the authorization endpoint of the Provider.
     *
     * The optional parameters passed to this method get merged with the data prepared by the implementation
     * and then passed to the $redirect callable. The first argument will always be the generated URI the user
     * should be redirected to to properly authorize the application's access, but additional parameters may
     * be passed along in any number and order, left at the implementation's discretion.
     *
     * @param   callable    $redirect   The callback that will perform the actual redirection logic.
     * @param   array       $parameters Additional parameters to account for when building the authorization URI.
     * @return  mixed
     */
    public function authorize(callable $redirect, array $parameters = []);

    /**
     * Returns the base authorization URL of the Provider, optionally including the given query parameters.
     *
     * @return  string
     */
    public function getAuthorizeUrl(array $parameters = []) : string;

    /**
     * Returns the token exchange URL of the Provider.
     *
     * @return  string
     */
    public function getExchangeUrl() : string;

    /**
     * Returns the identify URL of the Provider, if its available.
     *
     * In general this endpoint will allow an authorized application to query information about the entity whose
     * credentials (bearer token, for example) the application is using to access the Provider's services.
     *
     * @return string
     */
    public function getIdentifyUrl() : string;

    /**
     * Performs an *asynchronous* HTTP request to the specified URL.
     *
     * The request will be authenticated using the specified Token (actual type of Token required will depend
     * on the implementation), if it is given. Do note that while the Token is optional on an interface level,
     * many Provider/request combinations will require the request to be an authenticated one to succeed.
     *
     * Implementations are not required to recognize which requests need to be authenticated - this is left to the
     * user. Implementations are, however, required to use authentication when a Token is given.
     *
     * In general this method will allow easy querying of APIs that rely on common authentication specs implemented
     * in this component, where concrete Provides can also be used as base API clients and easily extended with
     * custom service consumption logic.
     *
     * @param   string                  $method         The HTTP method (verb) of the request.
     * @param   string                  $url            The URL to query.
     * @param   auth\interfaces\Token   $token          The Token to use to authenticate the request.
     * @param   array                   $options        Additional request options (see Guzzle's documentation).
     * @return  \GuzzleHttp\Promise\PromiseInterface    A Promise for a result.
     */
    public function request(string $method, string $url, auth\interfaces\Token $token = null, array $options = []) : \GuzzleHttp\Promise\PromiseInterface;
}

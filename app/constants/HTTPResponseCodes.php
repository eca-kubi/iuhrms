<?php

final class HTTPResponseCodes
{
    const OK = 200;
    const BAD_REQUEST = 400;
    const NO_PERMISSION = 403;
    const PAGE_NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const CLIENT_REQUEST_TIMED_OUT = 408;
    const SERVER_ERROR = 500;
    const INVALID_RESPONSE_FROM_UPSTREAM_SERVER = 502;
    const UPSTREAM_SERVER_REQUEST_TIMED_OUT = 504;
}
<?php

    namespace App\Http\Middleware;

    use Closure;

    class EtagMiddleware
    {
        public function handle($request, Closure $next)
        {
            $response = $next($request);

            if ($request->isMethod('GET'))
            {

                $etag = md5($response->getContent());

                $requestETag = str_replace('"', '', $request->getETags());



                if ($requestETag && $requestETag[0] == $etag)
                {

                    // Modifies the response so that it conforms to the rules defined for a 304 status code.
                    $response->setNotModified();

                }

                $response->setETag($etag);


            }

            return $response;
        }
    }

?>
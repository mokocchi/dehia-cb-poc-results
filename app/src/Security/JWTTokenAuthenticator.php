<?php

namespace App\Security;

use App\Api\ApiProblem;
use App\Api\ApiProblemException;
use App\Api\ApiProblemResponseFactory;
use App\Entity\Token;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JWTTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtEncoder;
    private $em;
    private $apiProblemResponseFactory;

    public function __construct(JWTEncoderInterface $jwtEncoder, EntityManagerInterface $em,  ApiProblemResponseFactory $apiProblemResponseFactory)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->em = $em;
        $this->apiProblemResponseFactory = $apiProblemResponseFactory;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );
        $token = $extractor->extract($request);
        if (!$token) {
            return;
        }
        return $token;
    }
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $data = $this->jwtEncoder->decode($credentials);
        } catch (JWTDecodeFailureException $e) {
            $reason = $e->getReason();
            switch ($reason) {
                case JWTDecodeFailureException::EXPIRED_TOKEN:
                    $message = "Expired token";
                    break;
                case JWTDecodeFailureException::INVALID_TOKEN:
                    $message = "Invalid JWT";
                    break;
                case JWTDecodeFailureException::UNVERIFIED_TOKEN:
                    $message = "JWT couldn't be verified";
                    break;
                default:
                    break;
            }
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $message, "Ocurrió un error en la autenticación")
            );
        }

        $user = new Token();
        $user->setToken($credentials);
        $user->setUsername($data["name"]);
        return $user;
    }
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->apiProblemResponseFactory->createResponse(new ApiProblem(
            "400",
            "Bearer token missing",
            "Authentication error"
        ));
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // TODO: Implement onAuthenticationSuccess() method.
    }
    public function supportsRememberMe()
    {
        // TODO: Implement supportsRememberMe() method.
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->apiProblemResponseFactory->createResponse(new ApiProblem(
            "401",
            "Gateway token missing",
            "Unauthorized"
        ));
    }
}

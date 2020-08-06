<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 17/12/2019
 * Time: 15:39
 */


namespace Messages;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;



class Authorization
{

    protected $acl;

    /**
     * used to set up authorization roles and pages roles can access
     * Authorization constructor.
     * @param $userRole
     */

    function __construct($userRole)
    {
        $this->userRole = $userRole;
        $this->acl = new Acl();

        $roleGuest = new Role('guest');
        $roleUser = new Role('user');
        $roleAdmin = new Role('admin');
        $this->admin = $roleAdmin;

        $this->acl->addRole($roleGuest);
        $this->acl->addRole($roleUser, $roleGuest);
        $this->acl->addRole($roleAdmin);

        $this->acl->addResource(new Resource('admin_interface'));
        $this->acl->addResource(new Resource('admin_edit_user'));
        $this->acl->addResource(new Resource('admin_edit_process'));
        $this->acl->addResource(new Resource('admin_delete_user'));
        $this->acl->addResource(new Resource('admin_delete_process'));


        $this->acl->deny(['user', 'guest'], ['admin_interface', 'admin_edit_user', 'admin_edit_process', 'admin_delete_user', 'admin_delete_process']);
        $this->acl->allow([$roleAdmin], ['admin_interface', 'admin_edit_user', 'admin_edit_process', 'admin_delete_user', 'admin_delete_process']);
    }

    /**
     * sets up route and checks that the user is allowed to access a given route
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $route = $request-> getAttribute('route');
        $pageName = $route->getName();

        if(!$this->acl -> isAllowed($this->userRole, $pageName)){
            return $response->withStatus(403);
        }
        $response = $next($request, $response);
        return $response;
    }
}
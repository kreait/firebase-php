# Using this library with Symfony

Configure the Base URL and Secret to your firebase application in `app/config/parameters.yml`:

```yaml
firebase.base_url: https://my_app.firebaseio.com
firebase.secret: <my_secret>
```

Define Firebase and its configuration in `app/config/services.yml`: 

```yaml
firebase.config:
    class: Kreait\Firebase\Configuration
    calls:
        - ['setFirebaseSecret', ['%firebase.secret%']]

firebase:
    class: Kreait\Firebase\Firebase
    arguments: ["%firebase.base_url%", '@firebase.config']
```

Usage example

```php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $this->get('firebase')->my()->reference()->set(['some' => 'data']);
        
        $data = $this->get('firebase')->my()->reference()->getData();

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
            'data' => $data
        ]);
    }
}
```

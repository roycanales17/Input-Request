<?php 

    require_once 'vendor/autoload.php';

    $req = new App\Http\Requests\Request();
    
    if ( $req->isMatched( 'submit', 'Login' ) )
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required'
        ])
        ->message([
            'email' => [
                'email' => 'Email address is invalid, given value ('.$req->input( 'email' ).').'
            ]
        ]);

        if ( $req->isSuccess() )
            exit( "Hello There!, ".$req->input( 'email' )."." );
        
        else 
        {
            echo "ERROR: <br>";
            foreach( $req->errors() as $key => $msg )
                echo "[$key] $msg<br>";
        }    
    }
?>

<form method="POST">
    <input type="email" name="email" placeholder="Email Address" value="<?=$req->input( 'email' )?>" />
    <span><?= $req->error( 'email' ) ?></span>
    <input type="password" name="password" placeholder="Password" value="<?=$req->input( 'password' )?>" />
    <span><?= $req->error( 'password' ) ?></span>
    <input type="submit" name="submit" value="Login" />
</form>

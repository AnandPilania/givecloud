<?php

namespace Tests\Unit\Mail;

use Ds\Mail\SubmitToEmail;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class SubmitToEmailTest extends TestCase
{
    use InteractsWithMailables;

    public function testMailablePreview(): void
    {
        $mailable = new SubmitToEmail('Nunc mollis facilisis dui varius', [
            'First Name' => 'Randy',
            'Last Name' => 'Marsh',
            'Email' => 'randy@example.com',
            'Message' => 'Vestibulum faucibus efficitur enim, nec pharetra libero posuere quis. Nam sit amet nisl purus. Aliquam at purus ut ante imperdiet ornare ac tincidunt magna. Ut finibus neque tempor, vehicula orci ac, facilisis quam. Nullam non hendrerit leo. Mauris sit amet dolor a mauris scelerisque efficitur. Aliquam erat volutpat. Pellentesque placerat feugiat vehicula. Nam convallis sit amet justo eget sodales. Integer congue scelerisque neque, vel finibus turpis consequat in.',
        ]);

        $this->assertMailablePreview($mailable);
    }
}

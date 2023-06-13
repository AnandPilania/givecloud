<?php
/**
 * Testing class taken from https://github.com/botman/studio-addons
 * With added type and getBotManCommand() method.
 */

namespace Tests\Fakes;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Commands\Command;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\BotMan\Messages\Attachments\Audio;
use BotMan\BotMan\Messages\Attachments\File;
use BotMan\BotMan\Messages\Attachments\Image;
use BotMan\BotMan\Messages\Attachments\Location;
use BotMan\BotMan\Messages\Attachments\Video;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Class BotManTester.
 */
class BotManTester
{
    /** @var \BotMan\BotMan\BotMan */
    private $bot;

    /** @var \BotMan\BotMan\Drivers\Tests\FakeDriver */
    private $driver;

    /** @var array */
    private $botMessages = [];

    /** @var string */
    private $user_id = '1';

    /** @var string */
    private $channel = '#botman';

    public function __construct(BotMan $bot, FakeDriver $driver)
    {
        $this->bot = $bot;
        $this->driver = $driver;
    }

    protected function listen(): void
    {
        $this->bot->listen();
        $this->driver->isInteractiveMessageReply = false;
    }

    protected function getReply(): ?OutgoingMessage
    {
        return array_shift($this->botMessages);
    }

    /**
     * @return \BotMan\BotMan\Messages\Outgoing\Question[]|string[]|Template[]
     */
    public function getMessages()
    {
        return $this->driver->getBotMessages();
    }

    public function getBotManCommand(): Command
    {
        return $this->bot->getCommand();
    }

    public function setDriver($driver): self
    {
        $this->driver->setName($driver::DRIVER_NAME);

        return $this;
    }

    public function setUser(array $user_info = []): self
    {
        $this->user_id = $user_info['id'] ?? $this->user_id;
        $this->driver->setUser($user_info);

        return $this;
    }

    public function setRecipient($channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function receivesRaw(IncomingMessage $message): self
    {
        $this->driver->messages = [$message];

        $this->driver->resetBotMessages();
        $this->listen();

        $this->botMessages = $this->getMessages();

        return $this;
    }

    public function receives(string $message, $payload = null): self
    {
        return $this->receivesRaw(new IncomingMessage($message, $this->user_id, $this->channel, $payload));
    }

    public function receivesInteractiveMessage(string $message, $payload = null): self
    {
        $this->driver->isInteractiveMessageReply = true;

        return $this->receives($message, $payload);
    }

    public function receivesLocation($latitude = 24, $longitude = 57): BotManTester
    {
        $message = new IncomingMessage(Location::PATTERN, $this->user_id, $this->channel);
        $message->setLocation(new Location($latitude, $longitude, null));

        return $this->receivesRaw($message);
    }

    public function receivesImages(array $urls = null): self
    {
        if (is_null($urls)) {
            $images = [new Image('https://via.placeholder.com/350x150')];
        } else {
            $images = Collection::make($urls)->map(function ($url) {
                return new Image(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Image::PATTERN, $this->user_id, $this->channel);
        $message->setImages($images);

        return $this->receivesRaw($message);
    }

    public function receivesAudio(array $urls = null): self
    {
        if (is_null($urls)) {
            $audio = [new Audio('https://www.youtube.com/watch?v=4zzSw-0IShE')];
        }
        if (is_array($urls)) {
            $audio = Collection::make($urls)->map(function ($url) {
                return new Audio(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Audio::PATTERN, $this->user_id, $this->channel);
        $message->setAudio($audio);

        return $this->receivesRaw($message);
    }

    public function receivesVideos(?array $urls = null): self
    {
        if (is_null($urls)) {
            $videos = [new Video('https://www.youtube.com/watch?v=4zzSw-0IShE')];
        } else {
            $videos = Collection::make($urls)->map(function ($url) {
                return new Video(($url));
            })->toArray();
        }
        $message = new IncomingMessage(Video::PATTERN, $this->user_id, $this->channel);
        $message->setVideos($videos);

        return $this->receivesRaw($message);
    }

    public function receivesFiles(?array $urls = null): self
    {
        if (is_null($urls)) {
            $files = [new File('https://www.youtube.com/watch?v=4zzSw-0IShE')];
        } else {
            $files = Collection::make($urls)->map(function ($url) {
                return new File(($url));
            })->toArray();
        }
        $message = new IncomingMessage(File::PATTERN, $this->user_id, $this->channel);
        $message->setFiles($files);

        return $this->receivesRaw($message);
    }

    public function receivesEvent($name, $payload = null): self
    {
        $this->driver->setEventName($name);
        $this->driver->setEventPayload($payload);

        $result = $this->receivesRaw(new IncomingMessage('', $this->user_id, $this->channel));

        $this->driver->setEventName(null);
        $this->driver->setEventPayload(null);

        return $result;
    }

    public function assertReply($message): self
    {
        $reply = $this->getReply();
        if ($reply instanceof OutgoingMessage) {
            PHPUnit::assertSame($message, $reply->getText());
        } else {
            PHPUnit::assertEquals($message, $reply);
        }

        return $this;
    }

    /**
     * Assert that there are specific multiple replies.
     */
    public function assertReplies(array $expectedMessages): self
    {
        $actualMessages = $this->getMessages();

        foreach ($actualMessages as $key => $actualMessage) {
            if ($actualMessage instanceof OutgoingMessage) {
                PHPUnit::assertSame($expectedMessages[$key], $actualMessage->getText());
            } else {
                PHPUnit::assertEquals($expectedMessages[$key], $actualMessage);
            }
        }

        return $this;
    }

    public function assertReplyIsNot(string $text): self
    {
        $message = $this->getReply();
        if ($message instanceof OutgoingMessage) {
            PHPUnit::assertNotSame($message->getText(), $text);
        } else {
            PHPUnit::assertNotEquals($message, $text);
        }

        array_unshift($this->botMessages, $message);

        return $this;
    }

    public function assertReplyIn(array $haystack): self
    {
        PHPUnit::assertTrue(in_array($this->getReply()->getText(), $haystack));

        return $this;
    }

    public function assertReplyNotIn(array $haystack): self
    {
        PHPUnit::assertFalse(in_array($this->getReply()->getText(), $haystack));

        return $this;
    }

    public function assertReplyNothing(): self
    {
        PHPUnit::assertNull($this->getReply());

        return $this;
    }

    public function assertQuestion($text = null): self
    {
        /** @var \BotMan\BotMan\Messages\Outgoing\Question $question */
        $question = $this->getReply();
        PHPUnit::assertInstanceOf(Question::class, $question);

        if (! is_null($text)) {
            PHPUnit::assertSame($text, $question->getText());
        }

        return $this;
    }

    public function assertTemplate(string $template, bool $strict = false): self
    {
        $message = $this->getReply();

        if ($strict) {
            PHPUnit::assertEquals($template, $message);
        } else {
            PHPUnit::assertInstanceOf($template, $message);
        }

        return $this;
    }

    public function assertTemplateIn(array $templates): self
    {
        $message = $this->getReply();
        PHPUnit::assertTrue(in_array($message, $templates));

        return $this;
    }

    public function assertTemplateNotIn(array $templates): self
    {
        $message = $this->getReply();
        PHPUnit::assertFalse(in_array($message, $templates));

        return $this;
    }

    public function assertRaw(OutgoingMessage $message): self
    {
        PHPUnit::assertSame($message, $this->getReply());

        return $this;
    }

    public function reply(int $times = 1): self
    {
        foreach (range(1, $times) as $time) {
            $this->getReply();
        }

        return $this;
    }
}

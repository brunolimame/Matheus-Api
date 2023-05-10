<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueEmbedVideo;
use PHPUnit\Framework\TestCase;


class EntityValueEmbedVideoTest extends TestCase
{
    public function testEmbedYoutube()
    {
        $entity = EntityValueEmbedVideo::factory('abc123', 'youtube');
        $this->assertInstanceOf(EntityValueEmbedVideo::class, $entity);
        $urlEmbed = "https://www.youtube.com/embed/abc123";
        $this->assertEquals($urlEmbed, $entity->embed);
        $this->assertEquals($urlEmbed . "?autoplay=1;mute=1", $entity->embedAuto);
    }

    public function testEmbedVimeo()
    {
        $entity = EntityValueEmbedVideo::factory('abc123', 'vimeo');
        $this->assertInstanceOf(EntityValueEmbedVideo::class, $entity);
        $urlEmbed = "https://player.vimeo.com/video/abc123";
        $this->assertEquals($urlEmbed, $entity->embed);
        $this->assertEquals($urlEmbed . "?autoplay=1&muted=1", $entity->embedAuto);
    }

    public function testEmbedFacebook()
    {
        $entity = EntityValueEmbedVideo::factory('abc123', 'facebook');
        $this->assertInstanceOf(EntityValueEmbedVideo::class, $entity);
        $urlEmbed = "https://www.facebook.com/plugins/video.php?href=abc123&show_text=false&width=734&height=411&appId";
        $this->assertEquals($urlEmbed, $entity->embed);
        $this->assertEquals($urlEmbed . "&autoplay=1", $entity->embedAuto);
    }
}

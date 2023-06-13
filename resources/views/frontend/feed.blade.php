{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss 
    xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" 
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    version="2.0">
<channel>
    <title>{{ $feed->name }}</title>
    <link>{{ $feed->rss_link }}</link>
    <atom:link href="{{ $feed->rss_link }}" rel="self" type="application/rss+xml" />
    <language>en-us</language>
    <description>{{ $feed->rss_description }}</description>
    @if ($feed->isitunes)
        <itunes:subtitle>{{ $feed->itunes_subtitle }}</itunes:subtitle>
        <itunes:author>{{ $feed->itunes_author }}</itunes:author>
        <itunes:summary>{{ $feed->rss_description }}</itunes:summary>
        <itunes:owner>
            <itunes:name>{{ $feed->itunes_owner_name }}</itunes:name>
            <itunes:email>{{ $feed->itunes_owner_email }}</itunes:email>
        </itunes:owner>
        <itunes:image href="{{ $feed->photo->thumbnail_url ?? '' }}" />
        <itunes:category text="{{ $feed->itunes_category }}" />
        <copyright>&#xA9; {{ $feed->rss_copyright }}</copyright>
    @endif
    @foreach ($posts as $post)
        @if ($feed->isitunes)
            @if ($post->enclosure)
                <item>
                    <title>{{ $post->name }}</title>
                    <itunes:author>{{ $post->author }}</itunes:author>
                    <itunes:subtitle></itunes:subtitle>
                    <itunes:summary>{{ $post->description }}</itunes:summary>
                    <enclosure url="{{ $post->enclosure->public_url }}" length="{{ $post->length_milliseconds }}" type="audio/mpeg" />
                    <guid>{{ $post->enclosure->public_url }}</guid>
                    <pubDate>{{ fromUtcFormat($post->postdatetime, 'r') }}</pubDate>
                    <itunes:duration>{{ $post->length_formatted }}</itunes:duration>
                    <itunes:keywords>{{ $post->tags }}</itunes:keywords>
                </item>
            @endif
        @else
            <item>
                <guid>{{ $post->absolute_url }}</guid>
                <title>{{ $post->name }}</title>
                <link>{{ $post->absolute_url }}</link>
                @if($post->description)
                    <description>{{ $post->description }}</description>
                @else
                    <description>{{ do_shortcode($post->body ?? '') }}</description>
                @endif
                @if ($post->featuredImage)
                    <enclosure url="{{ $post->featuredImage->public_url }}" length="{{ $post->featuredImage->size }}" type="{{ $post->featuredImage->content_type }}" />
                @endif
                <dc:creator>{{ $post->author }}</dc:creator>
                <dc:date>{{ fromUtcFormat($post->postdatetime, 'c') }}</dc:date>    
                <pubDate>{{ fromUtcFormat($post->postdatetime, 'r') }}</pubDate>    
            </item>
        @endif
    @endforeach
</channel>
</rss>

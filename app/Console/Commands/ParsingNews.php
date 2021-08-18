<?php

namespace App\Console\Commands;

use App\Models\Logging;
use Illuminate\Console\Command;

use App\Models\News;

use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use Symfony\Component\HttpFoundation\Request;


class ParsingNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing news';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $opts = array(
            'http' => array(
                'user_agent' => 'PHP libxml agent',
            )
        );

        
        
        $context = stream_context_create($opts);
        libxml_set_streams_context($context);
        
        $url = "http://static.feed.rbc.ru/rbc/logical/footer/news.rss"; // Адрес до RSS-ленты
        $rss = simplexml_load_file($url);

        $cntNode = News::count();

        foreach ($rss->channel->item as $item) {

            $k = News::where('Ссылка', $item->link)->count();

            if ($k == 0){

                $news = new News();

                $news->Название = $item->title;
                $news->Ссылка = $item->link;
                $news->КраткоеОписание = $item->description;
                $date = new DateTime($item->pubDate);
                $news->ДатаИВремяПубликации = $date->format('Y-m-d H:i:s');
                $news->Автор = $item->author;

                if ($item->enclosure['type'] == 'image/jpeg'){

                    $path = 'storage\app\public\\'. $cntNode .'.jpg';

                    file_put_contents($path, file_get_contents($item->enclosure['url']));
                    $news->Изображение = base64_encode(Storage::get('\public\\'. $cntNode .'.jpg'));  
        
                }

                $news->save();

                $cntNode += 1;

            }

        
        } 

        
    
    }



}

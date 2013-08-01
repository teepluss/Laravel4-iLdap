<?php

return array(

    'connections' => array(

        'default' => array(

            'host' => '10.0.0.3',
            'base' => 'o=clusters',
            'ou' => array(
                'users' => 'users'
            ),

            'bind_dn' => '',
            'bind_password' => ''
        )

    ),

    'schemas' => function($entries)
    {
        if (isset($entries['count']) and $entries['count'] > 0)
        {
            $stacks = array();

            for ($i=0; $i<$entries['count']; $i++)
            {
                $current = $entries[$i];

                $stack = array(
                    'uid'    => @$current['uid'][0] ?: null,
                    'dn'     => @$current['dn'] ?: null,
                    'mail'   => @$current['mail'][0] ?: null,
                    'name'   => @$current['cn'][0] ?: null,
                    'mobile' => @$current['mobile'][0] ?: null
                );

                array_push($stacks, $stack);
            }

            return $stacks;
        }
    }

);
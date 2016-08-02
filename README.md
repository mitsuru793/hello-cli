# hello-cli

色々な言語・ライブラリでhelloコマンドを作ります。

```
% hello
hello

% hello --japanese
こんにちは

% hello how are you?
hello, how are you?

% hello --to mike
Mike, hello

# デフォルトでhello.txtに出力します
% hello --write
% hello --write out.txt
```

## 引数・オプションでテストすること

* 引数は任意(helloに続く文章になる)
* オプション値が必須(-t, --to)
* オプション値は任意(-w, --write)
* 論理値のオプション(-j, --japanese)

## ファイル一覧

### php

#### simple.php

* [babarotさんのbashによるオプション解析](http://qiita.com/b4b4r07/items/dcd6be0bb9c9185475bb)を参考。
* テストが出来るように、エラー出力を例外クラスで対応。
* -abのように複数のオプションを一度に書くことはできない。

#### use_class.php

* simple.phpをクラス化。
* run()はstatic method。
* -abのように複数のオプションを一度に書くことはできない。

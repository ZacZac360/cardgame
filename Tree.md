C:\xampp\htdocs\cardgame>tree /f
Folder PATH listing for volume System
Volume serial number is 3E7B-6DED
C:.
│  .gitattributes
│  .gitignore
│  auth_action.php
│  cardgame.sql
│  composer.json
│  composer.lock
│  dashboard.php
│  forgot-password-verify.php
│  forgot-password.php
│  forgot_password_action.php
│  friends.php
│  guest_dashboard.php
│  index.php
│  logout.php
│  notifications.php
│  play.php
│  profile.php
│  reset-password.php
│  room.php
│  shop.php
│  solo.php
│  sql.txt
│  Tree.md
│  verify-login.php
│
├─admin
│      auth_action.php
│      index.php
│      login.php
│      pending-users.php
│      reports.php
│      settings.php
│      users.php
│
├─api
│  ├─2fa
│  │      backup-codes.php
│  │      setup.php
│  │      verify-setup.php
│  │
│  ├─email
│  │      send-otp.php
│  │      verify-login.php
│  │      verify-otp.php
│  │
│  ├─friends
│  │      accept.php
│  │      reject.php
│  │
│  ├─game
│  │      create_room.php
│  │      destroy_room.php
│  │      join_room.php
│  │      leave_room.php
│  │      pass_turn.php
│  │      play_card.php
│  │      reset_room.php
│  │      start_game.php
│  │      state.php
│  │      update_room.php
│  │
│  ├─messages
│  │      list.php
│  │      mark_read.php
│  │      send.php
│  │      thread.php
│  │
│  ├─notifications
│  │      read.php
│  │      read_all.php
│  │
│  └─payments
│          paymongo-topup-create.php
│          paymongo-topup-success.php
│
├─assets
│  │  adminstyle.css
│  │  cards.js
│  │  game-room.css
│  │  game-room.js
│  │  hub.css
│  │  main.js
│  │  style.css
│  │  userstyle.css
│  │
│  └─cards
│      └─fire
├─config
│      paymongo.php
│
├─includes
│      admin_ui.php
│      auth.php
│      db.php
│      friends_helpers.php
│      game_helpers.php
│      helpers.php
│      mail_config.php
│      messages_helpers.php
│      notification_action.php
│      profanity_helper.php
│      profile_helpers.php
│      ui.php
│
├─uploads
│  └─avatars
│          avatar_12_1773654943.png
│          avatar_3_1773395746.png
│          avatar_3_1773661284.png
│
└─vendor
    │  autoload.php
    │
    ├─bacon
    │  └─bacon-qr-code
    │      │  composer.json
    │      │  LICENSE
    │      │  README.md
    │      │
    │      └─src
    │          │  Writer.php
    │          │
    │          ├─Common
    │          │      BitArray.php
    │          │      BitMatrix.php
    │          │      BitUtils.php
    │          │      CharacterSetEci.php
    │          │      EcBlock.php
    │          │      EcBlocks.php
    │          │      ErrorCorrectionLevel.php
    │          │      FormatInformation.php
    │          │      Mode.php
    │          │      ReedSolomonCodec.php
    │          │      Version.php
    │          │
    │          ├─Encoder
    │          │      BlockPair.php
    │          │      ByteMatrix.php
    │          │      Encoder.php
    │          │      MaskUtil.php
    │          │      MatrixUtil.php
    │          │      QrCode.php
    │          │
    │          ├─Exception
    │          │      ExceptionInterface.php
    │          │      InvalidArgumentException.php
    │          │      OutOfBoundsException.php
    │          │      RuntimeException.php
    │          │      UnexpectedValueException.php
    │          │      WriterException.php
    │          │
    │          └─Renderer
    │              │  GDLibRenderer.php
    │              │  ImageRenderer.php
    │              │  PlainTextRenderer.php
    │              │  RendererInterface.php
    │              │
    │              ├─Color
    │              │      Alpha.php
    │              │      Cmyk.php
    │              │      ColorInterface.php
    │              │      Gray.php
    │              │      Rgb.php
    │              │
    │              ├─Eye
    │              │      CompositeEye.php
    │              │      EyeInterface.php
    │              │      ModuleEye.php
    │              │      PointyEye.php
    │              │      SimpleCircleEye.php
    │              │      SquareEye.php
    │              │
    │              ├─Image
    │              │      EpsImageBackEnd.php
    │              │      ImageBackEndInterface.php
    │              │      ImagickImageBackEnd.php
    │              │      SvgImageBackEnd.php
    │              │      TransformationMatrix.php
    │              │
    │              ├─Module
    │              │  │  DotsModule.php
    │              │  │  ModuleInterface.php
    │              │  │  RoundnessModule.php
    │              │  │  SquareModule.php
    │              │  │
    │              │  └─EdgeIterator
    │              │          Edge.php
    │              │          EdgeIterator.php
    │              │
    │              ├─Path
    │              │      Close.php
    │              │      Curve.php
    │              │      EllipticArc.php
    │              │      Line.php
    │              │      Move.php
    │              │      OperationInterface.php
    │              │      Path.php
    │              │
    │              └─RendererStyle
    │                      EyeFill.php
    │                      Fill.php
    │                      Gradient.php
    │                      GradientType.php
    │                      RendererStyle.php
    │
    ├─composer
    │      autoload_classmap.php
    │      autoload_namespaces.php
    │      autoload_psr4.php
    │      autoload_real.php
    │      autoload_static.php
    │      ClassLoader.php
    │      installed.json
    │      installed.php
    │      InstalledVersions.php
    │      LICENSE
    │      platform_check.php
    │
    ├─dasprid
    │  └─enum
    │      │  composer.json
    │      │  LICENSE
    │      │  README.md
    │      │
    │      └─src
    │          │  AbstractEnum.php
    │          │  EnumMap.php
    │          │  NullValue.php
    │          │
    │          └─Exception
    │                  CloneNotSupportedException.php
    │                  ExceptionInterface.php
    │                  ExpectationException.php
    │                  IllegalArgumentException.php
    │                  MismatchException.php
    │                  SerializeNotSupportedException.php
    │                  UnserializeNotSupportedException.php
    │
    ├─paragonie
    │  └─constant_time_encoding
    │      │  composer.json
    │      │  LICENSE.txt
    │      │  README.md
    │      │
    │      └─src
    │              Base32.php
    │              Base32Hex.php
    │              Base64.php
    │              Base64DotSlash.php
    │              Base64DotSlashOrdered.php
    │              Base64UrlSafe.php
    │              Binary.php
    │              EncoderInterface.php
    │              Encoding.php
    │              Hex.php
    │              RFC4648.php
    │
    └─pragmarx
        └─google2fa
            │  CHANGELOG.md
            │  composer.json
            │  LICENSE.md
            │  README.md
            │
            ├─.github
            │  └─workflows
            │          cross-platform.yml
            │          phpunit.yml
            │          static-analysis.yml
            │
            └─src
                │  Google2FA.php
                │
                ├─Exceptions
                │  │  Google2FAException.php
                │  │  IncompatibleWithGoogleAuthenticatorException.php
                │  │  InvalidAlgorithmException.php
                │  │  InvalidCharactersException.php
                │  │  SecretKeyTooShortException.php
                │  │
                │  └─Contracts
                │          Google2FA.php
                │          IncompatibleWithGoogleAuthenticator.php
                │          InvalidAlgorithm.php
                │          InvalidCharacters.php
                │          SecretKeyTooShort.php
                │
                └─Support
                        Base32.php
                        Constants.php
                        QRCode.php


C:\xampp\htdocs\cardgame>
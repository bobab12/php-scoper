<?php

/** @noinspection ClassConstantCanBeUsedInspection */

declare(strict_types=1);

/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Humbug\PhpScoper\Symbol;

use JetBrains\PHPStormStub\PhpStormStubsMap;
use function array_keys;
use function array_merge;

/**
 * @private
 */
final class Reflector
{
    private const MISSING_CLASSES = [
        // https://github.com/JetBrains/phpstorm-stubs/commit/18a771fcdff1af5b5e2d2f815f886316447bacc9
        'Swoole\Atomic' ,
        'Swoole\Atomic\Long' ,
        'Swoole\Client' ,
        'Swoole\Client\Exception' ,
        'Swoole\Connection\Iterator' ,
        'Swoole\Coroutine' ,
        'Swoole\Coroutine\Channel' ,
        'Swoole\Coroutine\Client' ,
        'Swoole\Coroutine\Context' ,
        'Swoole\Coroutine\Curl\Exception' ,
        'Swoole\Coroutine\Http2\Client' ,
        'Swoole\Coroutine\Http2\Client\Exception' ,
        'Swoole\Coroutine\Http\Client' ,
        'Swoole\Coroutine\Http\Client\Exception' ,
        'Swoole\Coroutine\Http\Server' ,
        'Swoole\Coroutine\Iterator' ,
        'Swoole\Coroutine\MySQL' ,
        'Swoole\Coroutine\MySQL\Exception' ,
        'Swoole\Coroutine\MySQL\Statement' ,
        'Swoole\Coroutine\Redis' ,
        'Swoole\Coroutine\Scheduler' ,
        'Swoole\Coroutine\Socket' ,
        'Swoole\Coroutine\Socket\Exception' ,
        'Swoole\Coroutine\System' ,
        'Swoole\Error' ,
        'Swoole\Event' ,
        'Swoole\Exception' ,
        'Swoole\ExitException' ,
        'Swoole\Http2\Request' ,
        'Swoole\Http2\Response' ,
        'Swoole\Http\Request' ,
        'Swoole\Http\Response' ,
        'Swoole\Http\Server' ,
        'Swoole\Lock' ,
        'Swoole\Process' ,
        'Swoole\Process\Pool' ,
        'Swoole\Redis\Server' ,
        'Swoole\Runtime' ,
        'Swoole\Server' ,
        'Swoole\Server\Event' ,
        'Swoole\Server\Packet' ,
        'Swoole\Server\PipeMessage' ,
        'Swoole\Server\Port' ,
        'Swoole\Server\StatusInfo' ,
        'Swoole\Server\Task' ,
        'Swoole\Server\TaskResult' ,
        'Swoole\Table' ,
        'Swoole\Timer' ,
        'Swoole\Timer\Iterator' ,
        'Swoole\WebSocket\CloseFrame' ,
        'Swoole\WebSocket\Frame' ,
        'Swoole\WebSocket\Server' ,

        // https://youtrack.jetbrains.com/issue/WI-29503
        'MongoInsertBatch',
        'MongoDeleteBatch',
    ];

    private const MISSING_FUNCTIONS = [
        // https://youtrack.jetbrains.com/issue/WI-53323
        'tideways_xhprof_enable',
        'tideways_xhprof_disable',

        // https://github.com/JetBrains/phpstorm-stubs/commit/18a771fcdff1af5b5e2d2f815f886316447bacc9
        'defer',
        'go',
        'swoole_async_dns_lookup_coro',
        'swoole_async_set',
        'swoole_clear_dns_cache',
        'swoole_clear_error',
        'swoole_client_select',
        'swoole_coroutine_create',
        'swoole_coroutine_defer',
        'swoole_coroutine_socketpair',
        'swoole_cpu_num',
        'swoole_errno',
        'swoole_error_log',
        'swoole_error_log_ex',
        'swoole_event_add',
        'swoole_event_cycle',
        'swoole_event_defer',
        'swoole_event_del',
        'swoole_event_dispatch',
        'swoole_event_exit',
        'swoole_event_isset',
        'swoole_event_set',
        'swoole_event_wait',
        'swoole_event_write',
        'swoole_get_local_ip',
        'swoole_get_local_mac',
        'swoole_get_mime_type',
        'swoole_get_object_by_handle',
        'swoole_get_objects',
        'swoole_get_vm_status',
        'swoole_hashcode',
        'swoole_ignore_error',
        'swoole_internal_call_user_shutdown_begin',
        'swoole_last_error',
        'swoole_mime_type_add',
        'swoole_mime_type_delete',
        'swoole_mime_type_exists',
        'swoole_mime_type_get',
        'swoole_mime_type_list',
        'swoole_mime_type_set',
        'swoole_select',
        'swoole_set_process_name',
        'swoole_strerror',
        'swoole_substr_json_decode',
        'swoole_substr_unserialize',
        'swoole_test_kernel_coroutine',
        'swoole_timer_after',
        'swoole_timer_clear',
        'swoole_timer_clear_all',
        'swoole_timer_exists',
        'swoole_timer_info',
        'swoole_timer_list',
        'swoole_timer_set',
        'swoole_timer_stats',
        'swoole_timer_tick',
        'swoole_version',

        // https://youtrack.jetbrains.com/issue/WI-29503
        'bson_encode',
        'bson_decode',
    ];

    /**
     * Basically mirrors https://github.com/nikic/PHP-Parser/blob/9aebf377fcdf205b2156cb78c0bd6e7b2003f106/lib/PhpParser/Lexer.php#L430
     */
    private const MISSING_CONSTANTS = [
        'STDIN',
        'STDOUT',
        'STDERR',

        // https://github.com/humbug/php-scoper/issues/618
        'true',
        'TRUE',
        'false',
        'FALSE',
        'null',
        'NULL',

        // Added in PHP 8.0
        'T_NAME_QUALIFIED',
        'T_NAME_FULLY_QUALIFIED',
        'T_NAME_RELATIVE',
        'T_MATCH',
        'T_NULLSAFE_OBJECT_OPERATOR',
        'T_ATTRIBUTE',

        // Added in PHP 8.1
        'T_ENUM',
        'T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG',
        'T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG',
        'T_READONLY',

        // https://youtrack.jetbrains.com/issue/WI-53323
        'TIDEWAYS_XHPROF_FLAGS_MEMORY',
        'TIDEWAYS_XHPROF_FLAGS_MEMORY_MU',
        'TIDEWAYS_XHPROF_FLAGS_MEMORY_PMU',
        'TIDEWAYS_XHPROF_FLAGS_CPU',
        'TIDEWAYS_XHPROF_FLAGS_NO_BUILTINS',
        'TIDEWAYS_XHPROF_FLAGS_MEMORY_ALLOC',
        'TIDEWAYS_XHPROF_FLAGS_MEMORY_ALLOC_AS_MU',

        // https://github.com/JetBrains/phpstorm-stubs/commit/18a771fcdff1af5b5e2d2f815f886316447bacc9
        'SOCKET_ECANCELED',
        'SWOOLE_ASYNC',
        'SWOOLE_BASE',
        'SWOOLE_CHANNEL_CANCELED',
        'SWOOLE_CHANNEL_CLOSED',
        'SWOOLE_CHANNEL_OK',
        'SWOOLE_CHANNEL_TIMEOUT',
        'SWOOLE_CORO_END',
        'SWOOLE_CORO_INIT',
        'SWOOLE_CORO_MAX_NUM_LIMIT',
        'SWOOLE_CORO_RUNNING',
        'SWOOLE_CORO_WAITING',
        'SWOOLE_DEBUG',
        'SWOOLE_DEFAULT_MAX_CORO_NUM',
        'SWOOLE_DISPATCH_CO_CONN_LB',
        'SWOOLE_DISPATCH_CO_REQ_LB',
        'SWOOLE_DISPATCH_FDMOD',
        'SWOOLE_DISPATCH_IDLE_WORKER',
        'SWOOLE_DISPATCH_IPMOD',
        'SWOOLE_DISPATCH_RESULT_CLOSE_CONNECTION',
        'SWOOLE_DISPATCH_RESULT_DISCARD_PACKET',
        'SWOOLE_DISPATCH_RESULT_USERFUNC_FALLBACK',
        'SWOOLE_DISPATCH_ROUND',
        'SWOOLE_DISPATCH_STREAM',
        'SWOOLE_DISPATCH_UIDMOD',
        'SWOOLE_DISPATCH_USERFUNC',
        'SWOOLE_DTLS_CLIENT_METHOD',
        'SWOOLE_DTLS_SERVER_METHOD',
        'SWOOLE_ERROR_AIO_BAD_REQUEST',
        'SWOOLE_ERROR_AIO_CANCELED',
        'SWOOLE_ERROR_AIO_TIMEOUT',
        'SWOOLE_ERROR_BAD_IPV6_ADDRESS',
        'SWOOLE_ERROR_CLIENT_NO_CONNECTION',
        'SWOOLE_ERROR_CO_BLOCK_OBJECT_LOCKED',
        'SWOOLE_ERROR_CO_BLOCK_OBJECT_WAITING',
        'SWOOLE_ERROR_CO_CANCELED',
        'SWOOLE_ERROR_CO_CANNOT_CANCEL',
        'SWOOLE_ERROR_CO_DISABLED_MULTI_THREAD',
        'SWOOLE_ERROR_CO_GETCONTEXT_FAILED',
        'SWOOLE_ERROR_CO_HAS_BEEN_BOUND',
        'SWOOLE_ERROR_CO_HAS_BEEN_DISCARDED',
        'SWOOLE_ERROR_CO_IOCPINIT_FAILED',
        'SWOOLE_ERROR_CO_MAKECONTEXT_FAILED',
        'SWOOLE_ERROR_CO_MUTEX_DOUBLE_UNLOCK',
        'SWOOLE_ERROR_CO_NOT_EXISTS',
        'SWOOLE_ERROR_CO_OUT_OF_COROUTINE',
        'SWOOLE_ERROR_CO_PROTECT_STACK_FAILED',
        'SWOOLE_ERROR_CO_STD_THREAD_LINK_ERROR',
        'SWOOLE_ERROR_CO_SWAPCONTEXT_FAILED',
        'SWOOLE_ERROR_CO_TIMEDOUT',
        'SWOOLE_ERROR_CO_YIELD_FAILED',
        'SWOOLE_ERROR_DATA_LENGTH_TOO_LARGE',
        'SWOOLE_ERROR_DNSLOOKUP_DUPLICATE_REQUEST',
        'SWOOLE_ERROR_DNSLOOKUP_NO_SERVER',
        'SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED',
        'SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT',
        'SWOOLE_ERROR_DNSLOOKUP_UNSUPPORTED',
        'SWOOLE_ERROR_EVENT_SOCKET_REMOVED',
        'SWOOLE_ERROR_FILE_EMPTY',
        'SWOOLE_ERROR_FILE_NOT_EXIST',
        'SWOOLE_ERROR_FILE_TOO_LARGE',
        'SWOOLE_ERROR_HTTP2_STREAM_ID_TOO_BIG',
        'SWOOLE_ERROR_HTTP2_STREAM_IGNORE',
        'SWOOLE_ERROR_HTTP2_STREAM_NOT_FOUND',
        'SWOOLE_ERROR_HTTP2_STREAM_NO_HEADER',
        'SWOOLE_ERROR_HTTP_INVALID_PROTOCOL',
        'SWOOLE_ERROR_HTTP_PROXY_BAD_RESPONSE',
        'SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_ERROR',
        'SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_FAILED',
        'SWOOLE_ERROR_INVALID_PARAMS',
        'SWOOLE_ERROR_MALLOC_FAIL',
        'SWOOLE_ERROR_NAME_TOO_LONG',
        'SWOOLE_ERROR_OPERATION_NOT_SUPPORT',
        'SWOOLE_ERROR_OUTPUT_BUFFER_OVERFLOW',
        'SWOOLE_ERROR_OUTPUT_SEND_YIELD',
        'SWOOLE_ERROR_PACKAGE_LENGTH_NOT_FOUND',
        'SWOOLE_ERROR_PACKAGE_LENGTH_TOO_LARGE',
        'SWOOLE_ERROR_PACKAGE_MALFORMED_DATA',
        'SWOOLE_ERROR_PHP_FATAL_ERROR',
        'SWOOLE_ERROR_PROTOCOL_ERROR',
        'SWOOLE_ERROR_QUEUE_FULL',
        'SWOOLE_ERROR_SERVER_CONNECT_FAIL',
        'SWOOLE_ERROR_SERVER_INVALID_COMMAND',
        'SWOOLE_ERROR_SERVER_INVALID_LISTEN_PORT',
        'SWOOLE_ERROR_SERVER_INVALID_REQUEST',
        'SWOOLE_ERROR_SERVER_MUST_CREATED_BEFORE_CLIENT',
        'SWOOLE_ERROR_SERVER_NO_IDLE_WORKER',
        'SWOOLE_ERROR_SERVER_ONLY_START_ONE',
        'SWOOLE_ERROR_SERVER_PIPE_BUFFER_FULL',
        'SWOOLE_ERROR_SERVER_SEND_IN_MASTER',
        'SWOOLE_ERROR_SERVER_TOO_MANY_LISTEN_PORT',
        'SWOOLE_ERROR_SERVER_TOO_MANY_SOCKET',
        'SWOOLE_ERROR_SERVER_WORKER_ABNORMAL_PIPE_DATA',
        'SWOOLE_ERROR_SERVER_WORKER_EXIT_TIMEOUT',
        'SWOOLE_ERROR_SERVER_WORKER_TERMINATED',
        'SWOOLE_ERROR_SERVER_WORKER_UNPROCESSED_DATA',
        'SWOOLE_ERROR_SESSION_CLOSED',
        'SWOOLE_ERROR_SESSION_CLOSED_BY_CLIENT',
        'SWOOLE_ERROR_SESSION_CLOSED_BY_SERVER',
        'SWOOLE_ERROR_SESSION_CLOSING',
        'SWOOLE_ERROR_SESSION_DISCARD_DATA',
        'SWOOLE_ERROR_SESSION_DISCARD_TIMEOUT_DATA',
        'SWOOLE_ERROR_SESSION_INVALID_ID',
        'SWOOLE_ERROR_SESSION_NOT_EXIST',
        'SWOOLE_ERROR_SOCKET_CLOSED',
        'SWOOLE_ERROR_SOCKET_POLL_TIMEOUT',
        'SWOOLE_ERROR_SOCKS5_AUTH_FAILED',
        'SWOOLE_ERROR_SOCKS5_HANDSHAKE_FAILED',
        'SWOOLE_ERROR_SOCKS5_SERVER_ERROR',
        'SWOOLE_ERROR_SOCKS5_UNSUPPORT_METHOD',
        'SWOOLE_ERROR_SOCKS5_UNSUPPORT_VERSION',
        'SWOOLE_ERROR_SSL_BAD_CLIENT',
        'SWOOLE_ERROR_SSL_BAD_PROTOCOL',
        'SWOOLE_ERROR_SSL_CANNOT_USE_SENFILE',
        'SWOOLE_ERROR_SSL_EMPTY_PEER_CERTIFICATE',
        'SWOOLE_ERROR_SSL_HANDSHAKE_FAILED',
        'SWOOLE_ERROR_SSL_NOT_READY',
        'SWOOLE_ERROR_SSL_RESET',
        'SWOOLE_ERROR_SSL_VERIFY_FAILED',
        'SWOOLE_ERROR_SYSTEM_CALL_FAIL',
        'SWOOLE_ERROR_TASK_DISPATCH_FAIL',
        'SWOOLE_ERROR_TASK_PACKAGE_TOO_BIG',
        'SWOOLE_ERROR_TASK_TIMEOUT',
        'SWOOLE_ERROR_UNREGISTERED_SIGNAL',
        'SWOOLE_ERROR_WEBSOCKET_BAD_CLIENT',
        'SWOOLE_ERROR_WEBSOCKET_BAD_OPCODE',
        'SWOOLE_ERROR_WEBSOCKET_HANDSHAKE_FAILED',
        'SWOOLE_ERROR_WEBSOCKET_INCOMPLETE_PACKET',
        'SWOOLE_ERROR_WEBSOCKET_PACK_FAILED',
        'SWOOLE_ERROR_WEBSOCKET_UNCONNECTED',
        'SWOOLE_ERROR_WEBSOCKET_UNPACK_FAILED',
        'SWOOLE_ERROR_WRONG_OPERATION',
        'SWOOLE_EVENT_READ',
        'SWOOLE_EVENT_WRITE',
        'SWOOLE_EXIT_IN_COROUTINE',
        'SWOOLE_EXIT_IN_SERVER',
        'SWOOLE_EXTRA_VERSION',
        'SWOOLE_FILELOCK',
        'SWOOLE_HAVE_BROTLI',
        'SWOOLE_HAVE_COMPRESSION',
        'SWOOLE_HAVE_ZLIB',
        'SWOOLE_HOOK_ALL',
        'SWOOLE_HOOK_BLOCKING_FUNCTION',
        'SWOOLE_HOOK_CURL',
        'SWOOLE_HOOK_FILE',
        'SWOOLE_HOOK_NATIVE_CURL',
        'SWOOLE_HOOK_PROC',
        'SWOOLE_HOOK_SLEEP',
        'SWOOLE_HOOK_SOCKETS',
        'SWOOLE_HOOK_SSL',
        'SWOOLE_HOOK_STDIO',
        'SWOOLE_HOOK_STREAM_FUNCTION',
        'SWOOLE_HOOK_STREAM_SELECT',
        'SWOOLE_HOOK_TCP',
        'SWOOLE_HOOK_TLS',
        'SWOOLE_HOOK_UDG',
        'SWOOLE_HOOK_UDP',
        'SWOOLE_HOOK_UNIX',
        'SWOOLE_HTTP2_ERROR_CANCEL',
        'SWOOLE_HTTP2_ERROR_COMPRESSION_ERROR',
        'SWOOLE_HTTP2_ERROR_CONNECT_ERROR',
        'SWOOLE_HTTP2_ERROR_ENHANCE_YOUR_CALM',
        'SWOOLE_HTTP2_ERROR_FLOW_CONTROL_ERROR',
        'SWOOLE_HTTP2_ERROR_FRAME_SIZE_ERROR',
        'SWOOLE_HTTP2_ERROR_INADEQUATE_SECURITY',
        'SWOOLE_HTTP2_ERROR_INTERNAL_ERROR',
        'SWOOLE_HTTP2_ERROR_NO_ERROR',
        'SWOOLE_HTTP2_ERROR_PROTOCOL_ERROR',
        'SWOOLE_HTTP2_ERROR_REFUSED_STREAM',
        'SWOOLE_HTTP2_ERROR_SETTINGS_TIMEOUT',
        'SWOOLE_HTTP2_ERROR_STREAM_CLOSED',
        'SWOOLE_HTTP2_TYPE_CONTINUATION',
        'SWOOLE_HTTP2_TYPE_DATA',
        'SWOOLE_HTTP2_TYPE_GOAWAY',
        'SWOOLE_HTTP2_TYPE_HEADERS',
        'SWOOLE_HTTP2_TYPE_PING',
        'SWOOLE_HTTP2_TYPE_PRIORITY',
        'SWOOLE_HTTP2_TYPE_PUSH_PROMISE',
        'SWOOLE_HTTP2_TYPE_RST_STREAM',
        'SWOOLE_HTTP2_TYPE_SETTINGS',
        'SWOOLE_HTTP2_TYPE_WINDOW_UPDATE',
        'SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED',
        'SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT',
        'SWOOLE_HTTP_CLIENT_ESTATUS_SEND_FAILED',
        'SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET',
        'SWOOLE_IOV_MAX',
        'SWOOLE_IPC_MSGQUEUE',
        'SWOOLE_IPC_NONE',
        'SWOOLE_IPC_PREEMPTIVE',
        'SWOOLE_IPC_SOCKET',
        'SWOOLE_IPC_UNIXSOCK',
        'SWOOLE_IPC_UNSOCK',
        'SWOOLE_KEEP',
        'SWOOLE_LOG_DEBUG',
        'SWOOLE_LOG_ERROR',
        'SWOOLE_LOG_INFO',
        'SWOOLE_LOG_NONE',
        'SWOOLE_LOG_NOTICE',
        'SWOOLE_LOG_ROTATION_DAILY',
        'SWOOLE_LOG_ROTATION_EVERY_MINUTE',
        'SWOOLE_LOG_ROTATION_HOURLY',
        'SWOOLE_LOG_ROTATION_MONTHLY',
        'SWOOLE_LOG_ROTATION_SINGLE',
        'SWOOLE_LOG_TRACE',
        'SWOOLE_LOG_WARNING',
        'SWOOLE_MAJOR_VERSION',
        'SWOOLE_MINOR_VERSION',
        'SWOOLE_MUTEX',
        'SWOOLE_MYSQLND_CR_CANT_FIND_CHARSET',
        'SWOOLE_MYSQLND_CR_COMMANDS_OUT_OF_SYNC',
        'SWOOLE_MYSQLND_CR_CONNECTION_ERROR',
        'SWOOLE_MYSQLND_CR_INVALID_BUFFER_USE',
        'SWOOLE_MYSQLND_CR_INVALID_PARAMETER_NO',
        'SWOOLE_MYSQLND_CR_MALFORMED_PACKET',
        'SWOOLE_MYSQLND_CR_NOT_IMPLEMENTED',
        'SWOOLE_MYSQLND_CR_NO_PREPARE_STMT',
        'SWOOLE_MYSQLND_CR_OUT_OF_MEMORY',
        'SWOOLE_MYSQLND_CR_PARAMS_NOT_BOUND',
        'SWOOLE_MYSQLND_CR_SERVER_GONE_ERROR',
        'SWOOLE_MYSQLND_CR_SERVER_LOST',
        'SWOOLE_MYSQLND_CR_UNKNOWN_ERROR',
        'SWOOLE_PROCESS',
        'SWOOLE_REDIS_ERR_ALLOC',
        'SWOOLE_REDIS_ERR_CLOSED',
        'SWOOLE_REDIS_ERR_EOF',
        'SWOOLE_REDIS_ERR_IO',
        'SWOOLE_REDIS_ERR_NOAUTH',
        'SWOOLE_REDIS_ERR_OOM',
        'SWOOLE_REDIS_ERR_OTHER',
        'SWOOLE_REDIS_ERR_PROTOCOL',
        'SWOOLE_REDIS_MODE_MULTI',
        'SWOOLE_REDIS_MODE_PIPELINE',
        'SWOOLE_REDIS_TYPE_HASH',
        'SWOOLE_REDIS_TYPE_LIST',
        'SWOOLE_REDIS_TYPE_NOT_FOUND',
        'SWOOLE_REDIS_TYPE_SET',
        'SWOOLE_REDIS_TYPE_STRING',
        'SWOOLE_REDIS_TYPE_ZSET',
        'SWOOLE_RELEASE_VERSION',
        'SWOOLE_RWLOCK',
        'SWOOLE_SEM',
        'SWOOLE_SERVER_COMMAND_EVENT_WORKER',
        'SWOOLE_SERVER_COMMAND_MANAGER',
        'SWOOLE_SERVER_COMMAND_MASTER',
        'SWOOLE_SERVER_COMMAND_REACTOR_THREAD',
        'SWOOLE_SERVER_COMMAND_TASK_WORKER',
        'SWOOLE_SERVER_COMMAND_WORKER',
        'SWOOLE_SOCK_ASYNC',
        'SWOOLE_SOCK_SYNC',
        'SWOOLE_SOCK_TCP',
        'SWOOLE_SOCK_TCP6',
        'SWOOLE_SOCK_UDP',
        'SWOOLE_SOCK_UDP6',
        'SWOOLE_SOCK_UNIX_DGRAM',
        'SWOOLE_SOCK_UNIX_STREAM',
        'SWOOLE_SPINLOCK',
        'SWOOLE_SSL',
        'SWOOLE_SSL_DTLS',
        'SWOOLE_SSL_SSLv2',
        'SWOOLE_SSL_TLSv1',
        'SWOOLE_SSL_TLSv1_1',
        'SWOOLE_SSL_TLSv1_2',
        'SWOOLE_SSL_TLSv1_3',
        'SWOOLE_SSLv23_CLIENT_METHOD',
        'SWOOLE_SSLv23_METHOD',
        'SWOOLE_SSLv23_SERVER_METHOD',
        'SWOOLE_SSLv3_CLIENT_METHOD',
        'SWOOLE_SSLv3_METHOD',
        'SWOOLE_SSLv3_SERVER_METHOD',
        'SWOOLE_STRERROR_DNS',
        'SWOOLE_STRERROR_GAI',
        'SWOOLE_STRERROR_SWOOLE',
        'SWOOLE_STRERROR_SYSTEM',
        'SWOOLE_SYNC',
        'SWOOLE_TASK_CALLBACK',
        'SWOOLE_TASK_COROUTINE',
        'SWOOLE_TASK_NONBLOCK',
        'SWOOLE_TASK_NOREPLY',
        'SWOOLE_TASK_PEEK',
        'SWOOLE_TASK_SERIALIZE',
        'SWOOLE_TASK_TMPFILE',
        'SWOOLE_TASK_WAITALL',
        'SWOOLE_TCP',
        'SWOOLE_TCP6',
        'SWOOLE_TIMER_MAX_MS',
        'SWOOLE_TIMER_MAX_SEC',
        'SWOOLE_TIMER_MIN_MS',
        'SWOOLE_TIMER_MIN_SEC',
        'SWOOLE_TLS_CLIENT_METHOD',
        'SWOOLE_TLS_METHOD',
        'SWOOLE_TLS_SERVER_METHOD',
        'SWOOLE_TLSv1_1_CLIENT_METHOD',
        'SWOOLE_TLSv1_1_METHOD',
        'SWOOLE_TLSv1_1_SERVER_METHOD',
        'SWOOLE_TLSv1_2_CLIENT_METHOD',
        'SWOOLE_TLSv1_2_METHOD',
        'SWOOLE_TLSv1_2_SERVER_METHOD',
        'SWOOLE_TLSv1_CLIENT_METHOD',
        'SWOOLE_TLSv1_METHOD',
        'SWOOLE_TLSv1_SERVER_METHOD',
        'SWOOLE_TRACE_AIO',
        'SWOOLE_TRACE_ALL',
        'SWOOLE_TRACE_BUFFER',
        'SWOOLE_TRACE_CARES',
        'SWOOLE_TRACE_CHANNEL',
        'SWOOLE_TRACE_CLIENT',
        'SWOOLE_TRACE_CLOSE',
        'SWOOLE_TRACE_CONN',
        'SWOOLE_TRACE_CONTEXT',
        'SWOOLE_TRACE_COROUTINE',
        'SWOOLE_TRACE_CO_CURL',
        'SWOOLE_TRACE_CO_HTTP_SERVER',
        'SWOOLE_TRACE_EOF_PROTOCOL',
        'SWOOLE_TRACE_EVENT',
        'SWOOLE_TRACE_HTTP',
        'SWOOLE_TRACE_HTTP2',
        'SWOOLE_TRACE_HTTP_CLIENT',
        'SWOOLE_TRACE_LENGTH_PROTOCOL',
        'SWOOLE_TRACE_MEMORY',
        'SWOOLE_TRACE_MYSQL_CLIENT',
        'SWOOLE_TRACE_NORMAL',
        'SWOOLE_TRACE_PHP',
        'SWOOLE_TRACE_REACTOR',
        'SWOOLE_TRACE_REDIS_CLIENT',
        'SWOOLE_TRACE_SERVER',
        'SWOOLE_TRACE_SOCKET',
        'SWOOLE_TRACE_SSL',
        'SWOOLE_TRACE_TABLE',
        'SWOOLE_TRACE_TIMER',
        'SWOOLE_TRACE_WEBSOCKET',
        'SWOOLE_TRACE_WORKER',
        'SWOOLE_UDP',
        'SWOOLE_UDP6',
        'SWOOLE_UNIX_DGRAM',
        'SWOOLE_UNIX_STREAM',
        'SWOOLE_USE_HTTP2',
        'SWOOLE_USE_SHORTNAME',
        'SWOOLE_VERSION',
        'SWOOLE_VERSION_ID',
        'SWOOLE_WEBSOCKET_CLOSE_ABNORMAL',
        'SWOOLE_WEBSOCKET_CLOSE_DATA_ERROR',
        'SWOOLE_WEBSOCKET_CLOSE_EXTENSION_MISSING',
        'SWOOLE_WEBSOCKET_CLOSE_GOING_AWAY',
        'SWOOLE_WEBSOCKET_CLOSE_MESSAGE_ERROR',
        'SWOOLE_WEBSOCKET_CLOSE_MESSAGE_TOO_BIG',
        'SWOOLE_WEBSOCKET_CLOSE_NORMAL',
        'SWOOLE_WEBSOCKET_CLOSE_POLICY_ERROR',
        'SWOOLE_WEBSOCKET_CLOSE_PROTOCOL_ERROR',
        'SWOOLE_WEBSOCKET_CLOSE_SERVER_ERROR',
        'SWOOLE_WEBSOCKET_CLOSE_STATUS_ERROR',
        'SWOOLE_WEBSOCKET_CLOSE_TLS',
        'SWOOLE_WEBSOCKET_FLAG_COMPRESS',
        'SWOOLE_WEBSOCKET_FLAG_FIN',
        'SWOOLE_WEBSOCKET_FLAG_MASK',
        'SWOOLE_WEBSOCKET_FLAG_RSV1',
        'SWOOLE_WEBSOCKET_FLAG_RSV2',
        'SWOOLE_WEBSOCKET_FLAG_RSV3',
        'SWOOLE_WEBSOCKET_OPCODE_BINARY',
        'SWOOLE_WEBSOCKET_OPCODE_CLOSE',
        'SWOOLE_WEBSOCKET_OPCODE_CONTINUATION',
        'SWOOLE_WEBSOCKET_OPCODE_PING',
        'SWOOLE_WEBSOCKET_OPCODE_PONG',
        'SWOOLE_WEBSOCKET_OPCODE_TEXT',
        'SWOOLE_WEBSOCKET_STATUS_ACTIVE',
        'SWOOLE_WEBSOCKET_STATUS_CLOSING',
        'SWOOLE_WEBSOCKET_STATUS_CONNECTION',
        'SWOOLE_WEBSOCKET_STATUS_HANDSHAKE',
        'SWOOLE_WORKER_BUSY',
        'SWOOLE_WORKER_EXIT',
        'SWOOLE_WORKER_IDLE',
        'WEBSOCKET_CLOSE_ABNORMAL',
        'WEBSOCKET_CLOSE_DATA_ERROR',
        'WEBSOCKET_CLOSE_EXTENSION_MISSING',
        'WEBSOCKET_CLOSE_GOING_AWAY',
        'WEBSOCKET_CLOSE_MESSAGE_ERROR',
        'WEBSOCKET_CLOSE_MESSAGE_TOO_BIG',
        'WEBSOCKET_CLOSE_NORMAL',
        'WEBSOCKET_CLOSE_POLICY_ERROR',
        'WEBSOCKET_CLOSE_PROTOCOL_ERROR',
        'WEBSOCKET_CLOSE_SERVER_ERROR',
        'WEBSOCKET_CLOSE_STATUS_ERROR',
        'WEBSOCKET_CLOSE_TLS',
        'WEBSOCKET_OPCODE_BINARY',
        'WEBSOCKET_OPCODE_CLOSE',
        'WEBSOCKET_OPCODE_CONTINUATION',
        'WEBSOCKET_OPCODE_PING',
        'WEBSOCKET_OPCODE_PONG',
        'WEBSOCKET_OPCODE_TEXT',
        'WEBSOCKET_STATUS_ACTIVE',
        'WEBSOCKET_STATUS_CLOSING',
        'WEBSOCKET_STATUS_CONNECTION',
        'WEBSOCKET_STATUS_FRAME',
        'WEBSOCKET_STATUS_HANDSHAKE',

        // https://youtrack.jetbrains.com/issue/WI-29503
        'MONGODB_VERSION',
        'MONGODB_STABILITY',
    ];

    private SymbolRegistry $classes;
    private SymbolRegistry $functions;
    private SymbolRegistry $constants;

    public static function createWithPhpStormStubs(): self
    {
        return new self(
            self::createSymbolList(
                array_keys(PhpStormStubsMap::CLASSES),
                self::MISSING_CLASSES,
            ),
            self::createSymbolList(
                array_keys(PhpStormStubsMap::FUNCTIONS),
                self::MISSING_FUNCTIONS,
            ),
            self::createConstantSymbolList(
                array_keys(PhpStormStubsMap::CONSTANTS),
                self::MISSING_CONSTANTS,
            ),
        );
    }

    public static function createEmpty(): self
    {
        return new self(
            SymbolRegistry::create(),
            SymbolRegistry::create(),
            SymbolRegistry::createForConstants(),
        );
    }

    private function __construct(
        SymbolRegistry $classes,
        SymbolRegistry $functions,
        SymbolRegistry $constants
    ) {
        $this->classes = $classes;
        $this->functions = $functions;
        $this->constants = $constants;
    }

    /**
     * @param string[] $classNames
     * @param string[] $functionNames
     * @param string[] $constantNames
     */
    public function withSymbols(
        array $classNames,
        array $functionNames,
        array $constantNames
    ): self
    {
        return new self(
            $this->classes->withAdditionalSymbols($classNames),
            $this->functions->withAdditionalSymbols($functionNames),
            $this->constants->withAdditionalSymbols($constantNames),
        );
    }

    public function isClassInternal(string $name): bool
    {
        return $this->classes->matches($name);
    }

    public function isFunctionInternal(string $name): bool
    {
        return $this->functions->matches($name);
    }

    public function isConstantInternal(string $name): bool
    {
        return $this->constants->matches($name);
    }

    /**
     * @param string[] $sources
     */
    private static function createSymbolList(array ...$sources): SymbolRegistry
    {
        return SymbolRegistry::create(
            array_merge(...$sources),
        );
    }

    /**
     * @param string[] $sources
     */
    private static function createConstantSymbolList(array ...$sources): SymbolRegistry
    {
        return SymbolRegistry::createForConstants(
            array_merge(...$sources),
        );
    }
}

<div class="media user-media">
    <a class="user-link" href="">
{{*        {{$imgAttribues = ['class' => 'media-object img-thumbnail user-img', 'alt' => 'User Picture', 'title' => 'User Picture']}}
        {{image('admin/user.gif', $imgAttribues)}}*}}
        <img src="{{$UserProfileImage}}" class="media-object img-thumbnail user-img" alt="User Picture" title="User Picture" style="width: 64px; height: 64px;" />
        <span class="label label-danger user-label">16</span>
    </a>
    <div class="media-body">
        <h5 class="media-heading">{{$this->session->userdata('FullName')}}</h5>
        <ul class="list-unstyled user-info">
            <li> <a href="">{{$UserGroupName}}</a> </li>
            <li>Last Access :
                <br>
                <small>
                    <i class="fa fa-calendar"></i>&nbsp;16 Mar 16:32</small>
            </li>
        </ul>
    </div>
</div>